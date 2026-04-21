import { describe, it, expect, beforeEach, vi } from 'vitest';
import fs from 'fs';
import path from 'path';

// Helper to load the tracker script into the JSDOM environment
const loadTracker = () => {
    const code = fs.readFileSync(path.resolve(__dirname, '../../resources/js/ga4-tracker.js'), 'utf8');
    const fn = new Function('window', 'document', code);
    fn(window, document);
};

describe('ga4-tracker.js', () => {
    beforeEach(() => {
        // Reset DOM and global window.ga4Marketing
        document.body.innerHTML = '';
        document.head.innerHTML = '';
        delete window.ga4Marketing;
        delete window.ga4Event;
        
        // Mock fetch
        global.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                json: () => Promise.resolve({ success: true }),
            })
        );

        loadTracker();
    });

    it('initializes with default config', () => {
        window.ga4Marketing.init();
        expect(window.ga4Marketing.config.route).toBe('/ga4-marketing/event');
    });

    it('can override config during init', () => {
        window.ga4Marketing.init({ route: '/custom/route' });
        expect(window.ga4Marketing.config.route).toBe('/custom/route');
    });

    it('sends page_view on init by default', () => {
        window.ga4Marketing.init();
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            method: 'POST',
            body: expect.stringContaining('"event_name":"page_view"')
        }));
    });

    it('does not send page_view if autoPageView is false', () => {
        window.ga4Marketing.init({ autoPageView: false });
        expect(global.fetch).not.toHaveBeenCalled();
    });

    it('does not send page_view if data-ga4-event="no-pageview" is on body', () => {
        document.body.setAttribute('data-ga4-event', 'no-pageview');
        window.ga4Marketing.init();
        expect(global.fetch).not.toHaveBeenCalled();
    });

    it('sends events manually via sendEvent', () => {
        window.ga4Marketing.init();
        window.ga4Marketing.sendEvent('test_event', { foo: 'bar' });
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"test_event"')
        }));
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"foo":"bar"')
        }));
    });

    it('sets up global ga4Event function', () => {
        window.ga4Marketing.init();
        window.ga4Event('global_event', { baz: 'qux' });
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"global_event"')
        }));
    });

    it('tracks clicks on elements with data-ga4-event', () => {
        window.ga4Marketing.init();
        const btn = document.createElement('button');
        btn.setAttribute('data-ga4-event', 'button_click');
        document.body.appendChild(btn);
        
        btn.click();
        
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"button_click"')
        }));
    });

    it('tracks outbound links', () => {
        window.ga4Marketing.init();
        const link = document.createElement('a');
        link.href = 'https://external.com/page';
        link.setAttribute('data-ga4-event', 'outbound');
        link.innerText = 'External Link';
        document.body.appendChild(link);

        // Prevent JSDOM from trying to navigate
        link.addEventListener('click', (e) => e.preventDefault());

        link.click();

        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"click"')
        }));
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"outbound":true')
        }));
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"link_url":"https://external.com/page"')
        }));
    });

    it('tracks visibility of elements', () => {
        let observerCallback;
        global.IntersectionObserver = vi.fn((callback) => {
            observerCallback = callback;
            return {
                observe: vi.fn(),
                unobserve: vi.fn(),
                disconnect: vi.fn(),
            };
        });

        const el = document.createElement('div');
        el.setAttribute('data-ga4-event', 'scroll');
        el.setAttribute('data-ga4-area', 'footer');
        document.body.appendChild(el);

        window.ga4Marketing.init();

        expect(global.IntersectionObserver).toHaveBeenCalled();

        // Simulate intersection
        observerCallback([{
            isIntersecting: true,
            target: el
        }]);

        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"scroll"')
        }));
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"visible_area":"footer"')
        }));
    });

    it('tracks clicks on elements with data-ga4 attribute (alias)', () => {
        window.ga4Marketing.init();
        const btn = document.createElement('button');
        btn.setAttribute('data-ga4', 'alias_click');
        document.body.appendChild(btn);
        
        btn.click();
        
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"alias_click"')
        }));
    });

    it('parses data-ga4-params attribute', () => {
        window.ga4Marketing.init();
        const btn = document.createElement('button');
        btn.setAttribute('data-ga4', 'params_click');
        btn.setAttribute('data-ga4-params', JSON.stringify({ key: 'value', num: 123 }));
        document.body.appendChild(btn);
        
        btn.click();
        
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"params_click"')
        }));
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"key":"value"')
        }));
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"num":123')
        }));
    });

    it('tracks Livewire events', () => {
        window.ga4Marketing.init();
        
        // Trigger ga4-event-triggered on document
        const event = new CustomEvent('ga4-event-triggered', {
            detail: { name: 'lw_event', params: { from: 'livewire' } }
        });
        document.dispatchEvent(event);
        
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"lw_event"')
        }));
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"from":"livewire"')
        }));
    });

    it('tracks Livewire events with positional arguments', () => {
        window.ga4Marketing.init();
        
        const event = new CustomEvent('ga4-event', {
            detail: ['positional_event', { p: 1 }]
        });
        document.dispatchEvent(event);
        
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"positional_event"')
        }));
    });

    it('tracks Livewire events with bridged payload', () => {
        window.ga4Marketing.init();
        
        const event = new CustomEvent('ga4-event', {
            detail: [{ name: 'bridged_event', params: { b: 2 } }]
        });
        document.dispatchEvent(event);
        
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"bridged_event"')
        }));
    });

    it('handles fetch error', () => {
        const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
        global.fetch = vi.fn(() => Promise.reject('Network Error'));
        
        window.ga4Marketing.init();
        window.ga4Marketing.sendEvent('error_event');
        
        return new Promise(resolve => setTimeout(resolve, 10)).then(() => {
            expect(consoleSpy).toHaveBeenCalledWith('GA4 Event Error:', 'Network Error');
            consoleSpy.mockRestore();
        });
    });

    it('handles malformed link URL', () => {
        window.ga4Marketing.init();
        const link = document.createElement('a');
        link.href = 'not-a-url';
        link.setAttribute('data-ga4-event', 'outbound');
        document.body.appendChild(link);
        
        link.click();
        
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"click"')
        }));
        // Should not crash and still send common link params
    });

    it('handles missing IntersectionObserver', () => {
        const originalIO = global.IntersectionObserver;
        delete global.IntersectionObserver;
        
        const el = document.createElement('div');
        el.setAttribute('data-ga4-event', 'scroll');
        document.body.appendChild(el);

        window.ga4Marketing.init();
        // Should not crash
        
        global.IntersectionObserver = originalIO;
    });

    it('tracks user engagement on beforeunload', () => {
        vi.useFakeTimers();
        window.ga4Marketing.init();
        
        vi.advanceTimersByTime(5000);
        
        window.dispatchEvent(new Event('beforeunload'));
        
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"event_name":"user_engagement"')
        }));
        expect(global.fetch).toHaveBeenCalledWith('/ga4-marketing/event', expect.objectContaining({
            body: expect.stringContaining('"engagement_time_msec":5000')
        }));
        
        vi.useRealTimers();
    });
});
