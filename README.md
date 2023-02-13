# OmmaComics

A web-comics hosting engine.

## Supported features

- Upload a chapter for your private preview.
- Edit the display name of a chapter.
- Publish uploaded chapters for the readers.
- Move chapters to the recycle bin and remove them permanently from there.

## Requirements
- PHP 8.0
- nginx
- node 16.3
- npm 8.6

## Installation
- Clone this repository to a directory.
- Add the nginx configuration from another repository: [ob-ivan/nginx-conf.d](https://github.com/ob-ivan/nginx-conf.d)
- Set up a webhook: on push to master, execute `public/githook.php`.
- Install crontab:
  ```
  * * * * * cd /home/ivan/.nvm; . ./nvm.sh ; cd /web/comics.ob-ivan.ru; nvm install >/dev/null 2>&1; nvm install-latest-npm >/dev/null 2>&1; ./deploy.sh
  ```

## Deployment

- `git pull`
- `composer install`
- `npm ci`
- `npm run build`

## Development

- This project uses `npm` to manage JavaScript dependencies.
- Please use `npm run dev` and `npm run watch` to create development builds.
