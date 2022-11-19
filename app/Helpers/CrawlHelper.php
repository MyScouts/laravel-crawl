<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use KubAT\PhpSimple\HtmlDomParser;

class CrawlHelper
{

    /**
     * removeAllElm
     *
     * @param  mixed $elm
     * @return void
     */
    private static function removeAllElm($elm)
    {
        foreach ($elm as $item) {
            $item->remove();
        }
    }

    /**
     * processingCrawl
     *
     * @param  mixed $url
     * @return void
     */
    public static function processingCrawl($url)
    {
        $headers = [
            'authority'                 => 'suchen.mobile.de',
            'accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'accept-language'           => 'en-US,en;q=0.9,vi;q=0.8',
            'cache-control'             => 'max-age=0',
            'sec-ch-ua'                 => '"Google Chrome";v="107", "Chromium";v="107", "Not=A?Brand";v="24"',
            'sec-ch-ua-mobile'          => '?0',
            'sec-ch-ua-platform'        => '"macOS"',
            'sec-fetch-dest'            => 'document',
            'sec-fetch-mode'            => 'navigate',
            'sec-fetch-site'            => 'cross-site',
            'sec-fetch-user'            => '?1',
            'upgrade-insecure-requests' => '1',
            'user-agent'                => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
            'Connection'                => 'keep-alive'
        ];

        $client = new Client(['allow_redirects' => ['track_redirects' => true]]);

        $request = new Request('GET', $url, $headers);
        $res = $client->sendAsync($request)->wait();

        $htmlStr = $res->getBody()->getContents();

        $dom = HtmlDomParser::str_get_html($htmlStr);

        // vehicle name processing
        $technical = $dom->find('.cBox-body--technical-data > div');
        $vehicleName = "";
        foreach ($technical as $child) {
            $keyTags = $child->find('div strong');
            $key = isset($keyTags[0]) ? $keyTags[0]->innertext : null;

            $child = $child->find('div');
            if ($key == 'Verfügbarkeit') {
                $vehicleName = end($child)->innertext;
                $vehicleName = strip_tags($vehicleName);
            }
        }

        // vehicle description processing
        $hrs = $dom->find('.cBox-body--vehicledescription .description hr');
        self::removeAllElm($hrs);
        $descriptionElm =  $dom->find('.cBox-body--vehicledescription .description');
        $vehicleDesc = "";
        if (isset($descriptionElm[0])) {
            $text = $descriptionElm[0]->innertext;
            @$text = preg_replace("/<(?:ul|br)[^>]*>/i", "\n", $text);
            $vehicleDesc = strip_tags($text);
        }

        return [$url, $vehicleName, $vehicleDesc];
    }
}
