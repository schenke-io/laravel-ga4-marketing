<?php

namespace SchenkeIo\LaravelGa4Marketing\Services;

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

        foreach ($this->getBotBlacklist() as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return true;
            }
        }

        return false;
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
