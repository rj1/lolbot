<?php
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use knivey\cmdr\attributes\CallWrap;
use knivey\cmdr\attributes\Cmd;
use knivey\cmdr\attributes\Syntax;
use Amp\Promise;

#[Cmd("yoda")]
#[Syntax('<url>...')]
#[CallWrap("Amp\asyncCall")]
function yoda_cmd($args, \Irc\Client $bot, \knivey\cmdr\Args $cmdArgs)
{
    $url = $cmdArgs['url'];

    if(!filter_var($url, FILTER_VALIDATE_URL)) return;

    try {
        $client = HttpClientBuilder::buildDefault();
        $request = new Request($url);

        /** @var Response $response */
        $response = yield $client->request($request);
        $body = yield $response->getBody()->buffer();
        if ($response->getStatus() != 200) {
            return;
        }

        $img = new Imagick();
        $img->readImageBlob($body);
        if(!$img->getImageFormat()) {
            return;
        }

        $yodaImg = new Imagick();
        $yodaImg->readImage(__DIR__ . "/yoda.png");
        $yodaImg->scaleImage($img->getImageWidth(), $img->getImageHeight());

        $img->compositeImage($yodaImg, Imagick::COMPOSITE_BLEND, 0, 0);

        $tmpfile = tempnam(sys_get_temp_dir(), 'yoda');
        $img->writeImage($tmpfile);
        $yodaPic = yield hostToFilehole($tmpfile);
        unlink($tmpfile);

        $bot->pm($args->chan,  $yodaPic);

    } catch (\Exception $e) {
        return;
    }
}

function hostToFilehole(string $filename): Promise
{
    return \Amp\call(function () use ($filename) {
        if(!file_exists($filename))
            throw new \Exception("hostToFilehole called with non existant filename: $filename");
        $client = HttpClientBuilder::buildDefault();
        $request = new Request("https://filehole.org", "POST");
        $body = new FormBody();
        $body->addField('url_len', '5');
        $body->addField('expiry', '86400');
        $body->addFile('file', $filename);
        $request->setBody($body);
        //var_dump($request);
        /** @var Response $response */
        $response = yield $client->request($request);
        //var_dump($response);
        if ($response->getStatus() != 200) {
            throw new \Exception("filehole.org returned {$response->getStatus()}");
        }
        $respBody = yield $response->getBody()->buffer();
        return $respBody;
    });
}
