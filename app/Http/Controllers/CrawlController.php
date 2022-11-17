<?php

namespace App\Http\Controllers;

use App\Exports\CrawlExport;
use App\Helpers\UploadHelper;
use App\Models\CrawlHistory;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use KubAT\PhpSimple\HtmlDomParser;
use Maatwebsite\Excel\Facades\Excel;

class CrawlController extends Controller
{

    public function removeAllElm($elm)
    {
        foreach ($elm as $item) {
            $item->remove();
        }
    }

    private function callAPI($url)
    {

        try {
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
            $this->removeAllElm($hrs);
            $descriptionElm =  $dom->find('.cBox-body--vehicledescription .description');
            $vehicleDesc = "";
            if (isset($descriptionElm[0])) {
                $text = $descriptionElm[0]->innertext;
                @$text = preg_replace("/<(?:ul|br)[^>]*>/i", "\n", $text);
                $vehicleDesc = strip_tags($text);
            }

            return [$url, $vehicleName, $vehicleDesc];
        } catch (\Throwable $th) {
            Log::error("CrawlController ::: callAPI", [
                'url'       => $url,
                'message'   => $th->getMessage()
            ]);
        }
        return null;
    }

    public function onCrawl()
    {
        $startTask = Carbon::now();

        $inputs = array(
            'https://suchen.mobile.de/auto-inserat/vw-arteon-elegance-2-0-tdi-dsg-474-mtl-app-connec-horn-bad-meinberg/303491853.html',
            'https://suchen.mobile.de/auto-inserat/renault-clio-equilibre-sce-65-achern/337538610.html',
            'https://suchen.mobile.de/auto-inserat/vw-arteon-2-0-tdi-dsg-489-mtl-navi-acc-led-horn-bad-meinberg/309545019.html',
            'https://suchen.mobile.de/auto-inserat/vw-arteon-shooting-brake-r-line-2-0-tdi-dsg-534-mt-horn-bad-meinberg/309545021.html',
            'https://suchen.mobile.de/auto-inserat/vw-arteon-shooting-brake-2-0-tdi-dsg-479-mtl-keyl-horn-bad-meinberg/309545024.html',
            'https://suchen.mobile.de/auto-inserat/vw-arteon-r-line-2-0-tdi-dsg-514-mtl-navi-acc-led-horn-bad-meinberg/309545047.html',
            'https://suchen.mobile.de/auto-inserat/audi-a4-av-40-tdi-qu-s-tronic-advanced-led-ahk-acc-wei%C3%9Fenburg/311945626.html',
            'https://suchen.mobile.de/auto-inserat/audi-q2-35-tfsi-s-tronic-s-line-panodach-ahk-navi-herrenberg/313237199.html',
            'https://suchen.mobile.de/auto-inserat/bmw-x2-sdrive-20-i-m-sport-park-assistent-led-navi-k-rostock/316164243.html',
            'https://suchen.mobile.de/auto-inserat/seat-ibiza-1-0-style-fse-usb-klima-pdc-shz-navigation-leverkusen/324185372.html'
        );

        foreach ($inputs as $input) {
            $result = $this->callAPI($input);
            if (!is_null($result)) $data[] = $result;
        }

        $export = new CrawlExport($data);

        $filePath =  CrawlHistory::FILE_PATH . "/" . date('Ymdhis') . "/" . CrawlHistory::FILE_NAME;

        Excel::store($export, $filePath);

        CrawlHistory::create([
            'file'          => $filePath,
            'total_task'    => count($inputs),
            'task_done'     => count($inputs),
            'finished_date' => Carbon::now(),
            'started_date'  => $startTask
        ]);

        // return Excel::download($export, CrawlHistory::FILE_NAME);
        return back()->with(['message' => 'Crawl is successfully!']);
    }
}
