<?

function PostCurl($url, $post, $auth)
{
    $ch = curl_init();


    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $request_headers = array();
    $request_headers[] = 'Content-Type: application/x-www-form-urlencoded';
    if ($auth) {
        $request_headers[] = 'Authorization: Bearer ' . $auth;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


function getImageMap()
{

    $actions = array(
        new \LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder('https://tw.yahoo.com/', new \LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(80, 80, 400, 400)),
        new \LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder('https://tw.yahoo.com/', new \LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(580, 80, 400, 400)),
        new \LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder('https://tw.yahoo.com/', new \LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(80, 580, 400, 400)),
        new \LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder('https://tw.yahoo.com/', new \LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(580, 580, 400, 400)),
    );

    $Imap = new \LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder("./PIC", "imagemap", new \LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder(1040, 1040), $actions);

    return $Imap;
}

;


function imageResizer($url, $width, $height)
{

    header('Content-type: image/jpeg');

    list($width_orig, $height_orig) = getimagesize($url);

    $ratio_orig = $width_orig / $height_orig;

    if ($width / $height > $ratio_orig) {
        $width = $height * $ratio_orig;
    } else {
        $height = $width / $ratio_orig;
    }

    $image_p = imagecreatetruecolor($width, $height);
    $image = imagecreatefromjpeg($url);
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

    imagejpeg($image_p, null, 100);

}


function getCustomTemplate()
{

    $text = "點開手機查看!";

    $actions = array(
        new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("★ Yahoo", "https://tw.yahoo.com/"),
        new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder("網址", "https://tw.yahoo.com/"),
        new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("Post Back", "You Click Post Back"),
    );

    $img_url = "https://scontent-tpe1-1.xx.fbcdn.net/v/t1.0-9/12235132_1019015721480727_2023233863562858122_n.jpg?oh=1afbb9523656e757023cd13020205d20&oe=596E7B45";

    $button = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder("標題", "文字", $img_url, $actions);

    $Template = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder($text, $button);

    return $Template;
}

;

function queryDB($queryString)
{
    $db = require 'db.php';
    $db->query("SET NAMES'UTF8'");
    $result = $db->query($queryString);
    $text = '';
    $arr = [];

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $obj = (object)[];

            foreach ($row as $key => $attribute) {
                $obj->$key = $attribute;
            }
            $arr[] = $obj;
        }

        $text = json_encode($arr);
    }

    $db->close();

    return $text;
}


//debug
error_reporting(E_ALL);
ini_set("display_errors", 1);
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$config = [
    'settings' => [
        'displayErrorDetails' => true,

        'logger' => [
            'name' => 'slim-app',
            'level' => Monolog\Logger::DEBUG,
            'path' => __DIR__ . '/../logs/app.log',
        ],
    ],
];


$app = new \Slim\App($config);
$container = $app->getContainer();
global $configs;
$configs = include('config.php');


$app->post('/LINE_POST', function (Request $request, Response $response) use ($configs) {


    $count = 0;
    $bot = new LINE\LINEBot(new \LINE\LINEBot\HTTPClient\CurlHTTPClient($configs->{'channelAccessToken'}), [
        'channelSecret' => $configs->{'channelSecret'},
    ]);

    $textMessageBuilder = getImageMap();
    $data = json_decode(queryDB("SELECT UserId FROM linechreg;"));

    foreach ($data as $userData) {
        if ($userData) {
            $line_response = $bot->pushMessage($userData->UserId, $textMessageBuilder);
            $count++;
        }

    }


    $response->getBody()->write("發送筆數:" . $count . "   " . $line_response->getRawBody());
    return $response;
});


$app->post('/LINE', function (Request $request, Response $response) use ($configs) {

    $channelAccessToken = $configs->{'channelAccessToken'};
    $channelSecret = $configs->{'$channelSecret'};
    $channelID = $configs->{'channelID'};
    $signature = $request->getHeader(LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE);

    if (empty($signature)) {
        $msgtext = 'Bad Request';
    } else {

        $bot = new LINE\LINEBot(new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channelAccessToken), [
            'channelSecret' => $channelSecret,
        ]);
        $events = $bot->parseEventRequest($request->getBody(), $signature[0]);

        foreach ($events as $event) {
            $reply_token = $event->getReplyToken();

            if ($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {
                $text = $event->getText();


                if (strstr($text, '#me')) {

                    $response = $bot->getProfile($event->getUserId());
                    if ($response->isSucceeded()) {
                        $profile = $response->getJSONDecodedBody();

                        $msg = 'displayName:' . $profile['displayName'] . ' pictureUrl:' . $profile['pictureUrl'] . ' statusMessage:' . $profile['statusMessage'];
                        $bot->replyText($reply_token, $msg);
                    }


                } else if (strstr($text, '#event')) {


                    if ($event->isRoomEvent()) {
                        $msg = "RoomId:" . $event->getRoomId();
                    } else if ($event->isUserEvent()) {
                        $msg = "UserId:" . $event->getUserId();
                    } else if ($event->isGroupEvent()) {
                        $msg = "GroupId:" . $event->getGroupId();
                    }
                    $bot->replyText($reply_token, $msg);


                } else if (strstr($text, '#fun')) {

                    $msg = getCustomTemplate();
                    $bot->replyMessage($reply_token, $msg);


                } else if (strstr($text, '#reg')) {

                    if ($event->isRoomEvent()) {
                        $ID = $event->getRoomId();
                    } else if ($event->isUserEvent()) {
                        $ID = $event->getUserId();
                    } else if ($event->isGroupEvent()) {
                        $ID = $event->getGroupId();
                    }

                    $response = $bot->getProfile($ID);
                    if ($response->isSucceeded()) {
                        $profile = $response->getJSONDecodedBody();
                        $displayName = $profile['displayName'];
                        $pictureUrl = $profile['pictureUrl'];
                        $result = queryDB("INSERT INTO `LineChReg`(`UserId`, `displayName`, `pictureUrl`) VALUES ('$ID','$displayName','$pictureUrl')");
                    }


                    $bot->replyText($reply_token, $result . '註冊成功');


                } else if (strstr($text, '#token')) {

                    $post = "grant_type=client_credentials&client_id=$channelID&client_secret=$channelSecret";
                    $url = "https://api.line.me/v2/oauth/accessToken";
                    $body = json_decode(PostCurl($url, $post, null));
                    $token = $body->{'access_token'};
                    $msg = '$token' . $token;

                    if ($token) {
                        $url = "https://api.line.me/v2/profile";
                        $body = json_decode(PostCurl($url, null, $token));
                        $msg = 'displayName: ' . $body->{'displayName'} . ' pictureUrl: ' . $body->{'pictureUrl'};
                    }

                    $bot->replyText($reply_token, $msg);

                } else if (strstr($text, '#user')) {

                    $url = "https://api.line.me/v2/oauth/accessToken/" . $event->getUserId();
                    $body = json_decode(PostCurl($url, null, $channelAccessToken));
                    $msg = 'displayName:' . $body->{'displayName'} . ' pictureUrl:' . $body->{'pictureUrl'} . ' statusMessage:' . $body->{'statusMessage'};
                    $bot->replyText($reply_token, $msg);

                } else if (strstr($text, '#menu')) {

                    $ImageMap = getImageMap();
                    $bot->replyMessage($reply_token, $ImageMap);

                }

            } else if ($event instanceof \LINE\LINEBot\Event\PostbackEvent) {
                $text = $event->getPostbackData();
                $bot->replyText($reply_token, $text);

            } else if ($event instanceof \LINE\LINEBot\Event\MessageEvent\LocationMessage) {
                $text = $event->getTitle() . " : " . $event->getAddress();
                $bot->replyText($reply_token, $text);
            }

        }
        $msgtext = 'Good Request';
    }


    $response->getBody()->write($msgtext);
    return $response;
});


$app->get('/PIC/{SIZE}', function (Request $request, Response $response) {

    $size = $request->getAttribute('SIZE');
    $url = "http://i.imgur.com/SJxeIjX.jpg";
    $image = imageResizer($url, $size, $size);

    $response->write($image);
    return $response->withHeader('Content-Type', FILEINFO_MIME_TYPE);
});

$app->run();

?>