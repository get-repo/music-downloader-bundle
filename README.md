# Music Downloader Bundle

## Installation
**Composer**
```bash
composer config repositories.get-repo/music-downloader-bundle git https://github.com/get-repo/music-downloader-bundle
composer require get-repo/music-downloader-bundle
```

**Update your `./app/AppKernel.php`**
```php
$bundles = [
    ...
    new GetRepo\MusicDownloaderBundle\MusicDownloaderBundle(),
    ...
];
```
or with
```bash
php -r "file_put_contents('./app/AppKernel.php', str_replace('];', \"    new GetRepo\MusicDownloaderBundle\MusicDownloaderBundle(),\n        ];\", file_get_contents('./app/AppKernel.php')));"
```

## Config
```yml
music_downloader:
    # Albums save path
    save_path: '%kernel.root_dir%/..'
    # CSS selectors
    sites:
        # bandcamp track
        bandcamp-track:
            url: /https:\/\/[^\.]+.bandcamp.com\/track\/.*/
            fetchers:
                youtube-dl:
                    output: false
            config: []
            # optional crawler class
            crawler_class: GetRepo\MusicDownloaderBundle\Crawler\AbstractCrawler\TrackCrawler
            # optional config class
            config_class: GetRepo\MusicDownloaderBundle\DependencyInjection\BandcampTrackConfiguration
        # bandcamp album
        bandcamp-album:
            url: /https:\/\/[^\.]+.bandcamp.com(\/album\/.*)?/
            fetchers:
                youtube-dl: ~
            config: []
            # optional crawler class
            crawler_class: GetRepo\MusicDownloaderBundle\Crawler\BandcampAlbumCrawler
            # optional config class
            config_class: GetRepo\MusicDownloaderBundle\DependencyInjection\BandcampAlbumConfiguration
        # deezer track
        deezer-track:
            url: /https:\/\/[^\.]+.deezer.com\/\w+\/track\/\d+/
            fetchers:
                mp3-cc: ~
            config: []
            # optional crawler class
            crawler_class: GetRepo\MusicDownloaderBundle\Crawler\AbstractCrawler\TrackCrawler
            # optional config class
            config_class: GetRepo\MusicDownloaderBundle\DependencyInjection\DeezerTrackConfiguration
```

## Command Line
```bash
php bin/console mad:download https://link.to/what/you/want
```
