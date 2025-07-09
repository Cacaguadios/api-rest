<?php
class YoutubeControlador
{
    static public function obtenerVideo($q)
    {
        $API_KEY = 'AIzaSyCiX4EW012jNOBcaQRPraG1tR8YLKwhV_s'; // tu API key

        // Construimos la URL de la petición
        $url = sprintf(
            'https://youtube.googleapis.com/youtube/v3/search?part=snippet&q=%s&maxResults=1&type=video&key=%s',
            urlencode($q),
            $API_KEY
        );

        // Hacemos la llamada HTTP
        $json = @file_get_contents($url);
        if ($json === false) {
            return ['error' => 'No se pudo conectar con YouTube API'];
        }

        $data = json_decode($json, true);
        if (empty($data['items'][0])) {
            return ['error' => 'No se encontraron videos'];
        }

        $item = $data['items'][0];
        $videoId   = $item['id']['videoId'] ?? null;
        $snippet   = $item['snippet'] ?? [];
        $title     = $snippet['title'] ?? '';
        $thumbnail = $snippet['thumbnails']['default']['url'] ?? '';

        return [
            'videoId'   => $videoId,
            'title'     => $title,
            'thumbnail' => $thumbnail,
            'url'       => 'https://www.youtube.com/watch?v=' . $videoId
        ];
    }
}
