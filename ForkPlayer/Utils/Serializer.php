<?php
/**
 * Created by PhpStorm.
 * User: Andrey
 * Date: 20.01.2017
 * Time: 14:42
 */

namespace ForkPlayer\Utils;


use DOMDocument;
use ForkPlayer\Playlist\Item;
use ForkPlayer\Playlist\ItemType;
use ForkPlayer\Playlist\Playlist;

class Serializer
{

    /**
     * Serializer constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param Playlist $playlist
     * @return string
     */
    public static function toXml($playlist)
    {
        $xml = new DOMDocument("1.0", "UTF-8");

        $channels = $playlist->getItems();

        $items = $xml->createElement("items");

        if (!empty($channels)) {
            $playlistName = $playlist->getName();

            if (!empty($playlistName)) {
                $elem = $xml->createElement("playlist_name");
                $elem->appendChild($xml->createCDATASection($playlistName));

                $items->appendChild($elem);
            }

            $nextPageUrl = $playlist->getNextPage();

            if (!empty($nextPageUrl)) {
                $elem = $xml->createElement("next_page_url");
                $elem->appendChild($xml->createCDATASection($nextPageUrl));

                $items->appendChild($elem);
            }

            foreach ($channels as $channel) {
                $chElement = $xml->createElement("channel");

                $elem = $xml->createElement("title");
                $elem->appendChild($xml->createCDATASection($channel->getName()));
                $chElement->appendChild($elem);

                $chDescription = $channel->getDescription();
                if (!empty($chDescription)) {
                    $elem = $xml->createElement("description");
                    $elem->appendChild($xml->createCDATASection($chDescription));
                    $chElement->appendChild($elem);
                }

                switch ($channel->getType()->get()) {
                    case ItemType::DIRECTORY:
                        $elem = $xml->createElement("playlist_url");
                        $elem->appendChild($xml->createCDATASection($channel->getLink()));
                        $chElement->appendChild($elem);
                        break;
                    case ItemType::FILE:
                        $elem = $xml->createElement("stream_url");
                        $elem->appendChild($xml->createCDATASection($channel->getLink()));
                        $chElement->appendChild($elem);
                        break;
                    case ItemType::SEARCH:
                        $elem = $xml->createElement("playlist_url");
                        $elem->appendChild($xml->createCDATASection($channel->getLink()));
                        $chElement->appendChild($elem);

                        $elem = $xml->createElement("search_on");
                        $elem->appendChild($xml->createTextNode("search"));
                        $chElement->appendChild($elem);
                        break;
                }

                $elem = $xml->createElement("logo_30x30");
                $elem->appendChild($xml->createCDATASection($channel->getImageLink()));
                $chElement->appendChild($elem);

                $items->appendChild($chElement);
            }
        }

        $xml->appendChild($items);

        return $xml->saveXML();
    }

    /**
     * @param Playlist $playlist
     * @return string
     */
    public static function toJson($playlist) {
        $channels = $playlist->getItems();

        $result = array();

        if (!empty($channels)) {
            $playlistName = $playlist->getName();

            if (!empty($playlistName)) {
                $result['playlist_name'] = $playlistName;
            }

            $nextPageUrl = $playlist->getNextPage();

            if (!empty($nextPageUrl)) {
                $result['next_page_url'] = $nextPageUrl;
            }

            $items = array();

            /**
             * @var Item channel
             */
            foreach ($channels as $channel) {
                $ch = array();

                $ch['title'] = $channel->getName();

                $description = $channel->getDescription();
                if (!empty($description)) {
                    $ch['description'] = $description;
                }

                switch ($channel->getType()->get()) {
                    case ItemType::DIRECTORY:
                        $ch['playlist_url'] = $channel->getLink();
                        break;
                    case ItemType::FILE:
                        $ch['stream_url'] = $channel->getLink();
                        break;
                    case ItemType::SEARCH:
                        $ch['playlist_url'] = $channel->getLink();
                        $ch['search_on'] = 'search';
                        break;
                }

                $ch['logo_30x30'] = $channel->getImageLink();

                array_push($items, $ch);
            }

            $result['channels'] = $items;
        }

        $res = json_encode($result, JSON_UNESCAPED_UNICODE);

        if ($res) {
            return $res;
        } else {
            return json_encode(array('channels' => array(array('title' => json_last_error_msg()))));
        }
    }
}