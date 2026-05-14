<?php

namespace SchenkeIo\LaravelGa4Marketing\Services;

/**
 * Service for mapping method arguments to GA4 event parameters.
 *
 * This class provides a centralized mapping logic for various
 * recommended and custom GA4 events, ensuring that PHP method
 * arguments are correctly transformed into the expected GA4
 * parameter structure.
 */
class EventMapper
{
    /**
     * Map method arguments to GA4 event parameters.
     *
     * @param  array<int, mixed>  $args
     * @return array<string, mixed>
     */
    public function mapArgumentsToParams(string $method, array $args): array
    {
        return match ($method) {
            'pageView' => [
                'page_location' => $args[0] ?? null,
                'page_title' => $args[1] ?? null,
                'page_referrer' => $args[2] ?? null,
                'language' => $args[3] ?? null,
            ],
            'click' => [
                'link_url' => $args[0] ?? null,
                'link_text' => $args[1] ?? null,
                'link_id' => $args[2] ?? null,
                'link_classes' => $args[3] ?? null,
                'link_domain' => $args[4] ?? null,
                'outbound' => $args[5] ?? null,
            ],
            'login', 'signUp' => ['method' => $args[0] ?? null],
            'share' => [
                'method' => $args[0] ?? null,
                'content_type' => $args[1] ?? null,
                'item_id' => $args[2] ?? null,
            ],
            'search' => ['search_term' => $args[0] ?? null],
            'viewItem', 'addToCart' => [
                'items' => $args[0] ?? [],
                'currency' => $args[1] ?? null,
                'value' => $args[2] ?? null,
            ],
            'beginCheckout' => [
                'items' => $args[0] ?? [],
                'currency' => $args[1] ?? null,
                'value' => $args[2] ?? null,
                'coupon' => $args[3] ?? null,
            ],
            'purchase' => [
                'transaction_id' => $args[0] ?? null,
                'items' => $args[1] ?? [],
                'value' => $args[2] ?? null,
                'currency' => $args[3] ?? null,
                'tax' => $args[4] ?? null,
                'shipping' => $args[5] ?? null,
                'coupon' => $args[6] ?? null,
            ],
            'scroll' => ['percent_scrolled' => $args[0] ?? null],
            'fileDownload' => [
                'file_name' => $args[0] ?? null,
                'file_extension' => $args[1] ?? null,
                'link_url' => $args[2] ?? null,
                'link_text' => $args[3] ?? null,
                'link_id' => $args[4] ?? null,
                'link_classes' => $args[5] ?? null,
                'link_domain' => $args[6] ?? null,
            ],
            'calculatorUsed' => $args[0] ?? [],
            default => [],
        };
    }
}
