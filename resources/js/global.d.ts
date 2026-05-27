interface Window {
    ga4MarketingInitialized?: boolean;
    ga4Event?: (eventName: string, eventParams: Record<string, any>) => void;
    ga4Marketing?: any;
}
