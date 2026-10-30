# Meme Compilation Generator Bot

This programme generates meme compilations using short memes from facebook.

Here, I currently use it on my own channel (hopefully the channel isn't taken down by the time you read this!) https://www.youtube.com/channel/UC7TxdnekJY040lHTgd-xThw   

Sadly graph api became a lot more limited recentely, and the bot can no longer download new videos, only recycle old ones randomly.

This wont work as is out the box, you need to go through the scripts and see where there are references to .mov or .mp4 or .png or .jpg files, say in ffmpeg commands embedded in the exec functions. You then need to add your own versions of these files, say a background video file or something, and change the name in the script (or name ur own file what its already called to avoid confusion). Couldn't upload all of them coz of github size limit and also get ur own files x.

Designed to run on Mac with localhost XAMPP w/ php 5.6.35 apache server. you need to get ur own facebook graph api credentials, set them in videoprocessing.php. I'm not even sure if you can still download videos from fb using their api any more. If not, find your own way of populating the tempDownloadedMemes folder in the rootdir with mp4s, then the bot should run. If not, contact me I can send you huge amount of video memes I have on my local machine (16 gb)
You need to run composer install to install the libraries. Also give the entire root dir recursive read write permissions for all users.

You may need to change some php config settings to allow for longer timeout idk, see what goes wrong, raise an issue if you have no clue.

This bot is designed to be used with another of my projects, an automated youtube upload bot that uploads at designated time gaps, with comments and description etc all automatically filled out using youtube data api: https://github.com/EthanSK/Youtube-Upload-Bot

## License

Source-available under [Ethan Source-Available No-Sale Terms 1.0](LICENSE). You can use and modify the covered code, including internally at work, and share it without charge. Selling the software, modified versions, paid access, or bundles requires a separate commercial license. Earlier licenses and third-party rights remain in force. See [scope and release policy](LICENSING.md).
