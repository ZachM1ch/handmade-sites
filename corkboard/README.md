# Corkboard

A cork-and-pushpin organization board for the browser. Pin post-its, labels, photos, documents and links, drag them around, layer them, tie them together with string, and keep separate named boards for each project.

## Install

Copy the `corkboard/` folder anywhere under your web root, for example `/var/www/html/corkboard/`, then open `https://your-site/corkboard/`. There is no build step and nothing to configure.

```
corkboard/
├── index.html      the app
├── corkboard.css
├── corkboard.js
├── api.php         optional: server-side storage + link previews
├── .htaccess       raises PHP upload limits on Apache/mod_php
├── .user.ini       raises PHP upload limits on PHP-FPM hosts
└── data/           boards and uploads end up here (server mode)
```

## Where boards are saved

The app checks for `api.php` when it starts:

| Your server | What happens |
|---|---|
| **PHP 7.4+ is available** and `data/` is writable | Boards and uploads are stored on the server in `data/`, so you see the same boards on every device. |
| **No PHP** (static hosting, nginx without PHP, etc.) | Boards are stored in the browser (IndexedDB). They stay on that device, in that browser. |

Open the ☰ Boards drawer to see which mode is active. Add `?local` to the URL to force browser storage.

If you're in server mode, the web server user has to be able to write to `data/`:

```sh
sudo chown -R www-data:www-data corkboard/data    # use your server's user
```

### Protect `data/` on nginx

Apache picks up `data/.htaccess` automatically. On nginx, add:

```nginx
location ^~ /corkboard/data/ { deny all; }
```

Uploads are saved with a `.bin` extension and served through `api.php`, so an uploaded file can never run as a script.

### Upload size

PHP's default upload limit is often only 2 MB. The included `.user.ini` and `.htaccess` raise it to 64 MB where the host allows that. If a large PDF fails, raise `upload_max_filesize` and `post_max_size` in `php.ini`. Photos larger than 2400 px are scaled down in the browser before upload.

## Security

**There's no login.** Anyone who can open the folder can see and edit every board. If the site is public, put the folder behind HTTP basic auth or your existing auth, or only expose it on your LAN or VPN.

`api.php`:
- requires a custom request header on every write, to block cross-site form attacks
- refuses link-preview fetches to private, loopback and reserved IP addresses
- serves HTML and SVG uploads only as downloads, in a sandbox

## Using it

- **Add things** from the toolbar, or drop or paste them straight onto the board. This works for image files, any document, URLs, and plain text (which becomes a post-it).
- **Move** an item by dragging it. **Pan** by dragging empty cork. **Zoom** with the wheel, by pinching, or with the buttons. Click the % button to fit everything on screen.
- **Strings:** drag from an item's pushpin to another item. You can also select an item, tap **String**, then tap the second item. Tap a string to change its colour or cut it.
- **Layers:** use the arrow buttons in the selection bar, or press `[` / `]` (with Shift to go all the way to the back or front).
- **Resize** with the corner handle (Shift frees a photo's aspect ratio). **Rotate** with the top handle (Shift snaps to 15°). **Straighten** resets the rotation.
- **Double-click** (or double-tap) to edit a post-it or label, enlarge a photo, open a document, or play a video.
- **Boards drawer:** create, rename, duplicate, delete, export to a `.corkboard.json` file (uploads included), and import.
- Keys: `N` post-it · `L` label · `Del` delete · `Ctrl+D` duplicate · `Ctrl+Z` / `Ctrl+Shift+Z` undo/redo · arrow keys nudge · `Esc` deselect

### Link previews

- **YouTube, Vimeo and Spotify** links show a thumbnail with a play button, and the video plays right on the card.
- **Other links** show a title, description and image. In server mode `api.php` reads the page's Open Graph tags. Otherwise the browser asks [noembed.com](https://noembed.com) and then [microlink.io](https://microlink.io). Microlink's free tier allows about 50 lookups a day.

### Document previews

- **PDFs** show their first page. This uses pdf.js, loaded from cdnjs the first time it's needed.
- **Text-like files** (txt, md, csv, code) show an excerpt.
- **Everything else** gets a labelled file card.

## Outside services

The app works without any of these. Without them, previews are simpler and the handwriting fonts fall back to system fonts.

- Google Fonts: Caveat and Special Elite
- Google's favicon service, for link icons
- noembed / microlink, for link previews without PHP
- cdnjs pdf.js, for PDF thumbnails
- youtube-nocookie.com, Vimeo and Spotify players, only when you press play

## Notes

- If two people edit the same board at the same time, the last save wins.
- Uploads that no board uses any more are cleaned up automatically after an hour.
