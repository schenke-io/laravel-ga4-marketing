<?php

namespace SchenkeIo\LaravelGa4Marketing\Services;

/**
 * Service for detecting search engine bots and crawlers.
 *
 * This class identifies common bots based on their User-Agent strings,
 * allowing the application to skip tracking for non-human traffic.
 */
class BotDetector
{
    private const BOT_LIST = 'Googlebot,Bingbot,Slurp,DuckDuckBot,Baiduspider,YandexBot,facebot,facebookexternalhit,ia_archiver,AdsBot-Google,Applebot,Embedly,Pinterestbot,Slackbot,Twitterbot,WhatsApp,ZoominfoBot,AhrefsBot,SemrushBot,DotBot,Rogerbot,MJ12bot,PetalBot,Bytespider,SEOkicks,UptimeRobot,Pingdom';

    /**
     * @param  string[]  $extraBots
     */
    public function __construct(private readonly array $extraBots = []) {}

    /**
     * Check if the given User-Agent belongs to a bot.
     */
    public function isBot(string $userAgent): bool
    {
        if ($userAgent === '') {
            return false;
        }

        $pattern = '/('.implode('|', array_map(fn ($bot) => preg_quote($bot, '/'), $this->getBotBlacklist())).')/i';

        return (bool) preg_match($pattern, $userAgent);
    }

    /**
     * Get the list of bot User Agents to ignore.
     *
     * @return array<int, string>
     */
    public function getBotBlacklist(): array
    {
        $builtIn = explode(',', self::BOT_LIST);

        // Deduplicate the list
        return array_values(array_unique(array_merge($builtIn, $this->extraBots)));
    }
}
