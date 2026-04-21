window.ga4Marketing = {
    config: {
        route: '/ga4-marketing/event',
        token: document.querySelector('meta[name="csrf-token"]')?.content,
        autoPageView: true
    },

    init: function(config = {}) {
        this.config = Object.assign(this.config, config);

        // 1. Page View
        if (this.config.autoPageView !== false && (document.body.getAttribute('data-ga4-event') !== 'no-pageview' && document.body.getAttribute('data-ga4') !== 'no-pageview')) {
            this.sendEvent('page_view', {
                page_location: window.location.href,
                page_title: document.title
            });
        }

        this.setupClickTracking();
        this.setupLivewireTracking();
        this.setupUserEngagementTracking();
        this.setupVisibilityTracking();

        window.ga4Event = (eventName, eventParams) => this.sendEvent(eventName, eventParams);
    },

    sendEvent: function(eventName, eventParams = {}) {
        return fetch(this.config.route, {
            method: 'POST',
            keepalive: true,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.config.token
            },
            body: JSON.stringify({
                event_name: eventName,
                event_params: eventParams
            })
        }).catch(error => console.error('GA4 Event Error:', error));
    },

    setupClickTracking: function() {
        document.addEventListener('click', (event) => {
            const target = event.target.closest('[data-ga4-event], [data-ga4]');
            if (target) {
                let eventName = target.getAttribute('data-ga4-event') || target.getAttribute('data-ga4');
                let eventParams = {};

                if (!eventName || eventName === 'no-pageview' || eventName === 'scroll') return;

                if (eventName === 'outbound' && target.tagName === 'A') {
                    eventName = 'click';
                    eventParams.outbound = true;
                    eventParams.link_url = target.href;
                    try {
                        const url = new URL(target.href);
                        eventParams.link_domain = url.hostname;
                    } catch (e) {}
                }

                try {
                    const params = target.getAttribute('data-ga4-params');
                    if (params) {
                        eventParams = Object.assign(eventParams, JSON.parse(params));
                    }
                } catch (e) {}

                if (target.tagName === 'A') {
                    if (!eventParams.link_url) eventParams.link_url = target.href;
                    if (!eventParams.link_text) eventParams.link_text = (target.innerText || target.textContent || '').trim();
                    if (!eventParams.link_id && target.id) eventParams.link_id = target.id;
                    if (!eventParams.link_classes && target.className) eventParams.link_classes = target.className;
                }

                this.sendEvent(eventName, eventParams);
            }
        });
    },

    setupLivewireTracking: function() {
        const handler = (event) => {
            const detail = event.detail;
            if (!detail) return;

            // 1. Positional arguments from $this->dispatch('ga4-event', 'name', {params})
            if (Array.isArray(detail) && typeof detail[0] === 'string') {
                this.sendEvent(detail[0], detail[1] || {});
            }
            // 2. Bridged event from server or array payload
            else if (Array.isArray(detail) && typeof detail[0] === 'object' && detail[0] !== null && detail[0].name) {
                this.sendEvent(detail[0].name, detail[0].params || {});
            }
            // 3. Named arguments or single object
            else if (typeof detail === 'object' && detail !== null && detail.name) {
                this.sendEvent(detail.name, detail.params || {});
            }
        };

        document.addEventListener('ga4-event', handler);
        document.addEventListener('ga4-event-triggered', handler);
    },

    setupUserEngagementTracking: function() {
        let startTime = Date.now();
        window.addEventListener('beforeunload', () => {
            this.sendEvent('user_engagement', {
                engagement_time_msec: Date.now() - startTime
            });
        });
    },

    setupVisibilityTracking: function() {
        if (typeof window !== 'undefined' && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target;
                        const area = target.getAttribute('data-ga4-area') || target.id || 'unknown';
                        this.sendEvent('scroll', {
                            visible_area: area
                        });
                        observer.unobserve(target);
                    }
                });
            });

            document.querySelectorAll('[data-ga4-event="scroll"], [data-ga4="scroll"]').forEach(el => {
                observer.observe(el);
            });
        }
    }
};
