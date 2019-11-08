|Group|Datum|Game data field|Discover field|App field|Details field|BrewyaOnOuya field|Example|Description|
|--- |--- |--- |--- |--- |--- |--- |--- |--- |
|app|Game title|title|title|title|title|App.title|Bloo Kid 2||
|app|Overview text|overview (optional)||overview|-|App.overview|Released in October 2015 by winterworks GmbH.||
|app|Description|description||description|description|App.description|Bloo Kid 2 is a classic 2D retro-style platformer experience with lovely designed pixel-graphics and a full chiptune soundtrack. Run, jump and swim your way through FIVE huge worlds with TWELVE levels each. Master brutal bossfights and discover lots of secrets in the world of Bloo Kid 2.\r\n\r\nBloo Kid 2 features:\r\n- five worlds with twelve levels each\r\n- handcrafted, colorful pixel-graphics\r\n- a full chiptune soundtrack\r\n- epic boss battles\r\n- tons of secrets\r\n- achievements||
|app|Number of players|players|gamerNumbers|gamerNumbers|gamerNumbers|App.playerNumbers|[1]|Any combination of 1-4|
|app|Genre list|genres|genres|genres|genres|App.genres|[Platformer, Retro]|original genre list: <br><br /> Adventure<br /> App<br /> Arcade/Pinball<br /> Card/Casino<br /> Dual Stick<br /> Entertainment<br /> Fight!<br /> FPS/Shooter<br /> Kids List<br /> Meditative<br /> Multiplayer<br /> Music<br /> Platformer<br /> Puzzle/Trivia<br /> Racing<br /> Retro<br /> Role-Playing<br /> Short on Time?<br /> Sim/Strategy<br /> Sports<br /> Utility<br /> Video|
|app|Package name|package|package||apk.package|App.packageName|evil.corptron.DuckGame|Run "aapt dump badging file.apk", field "package: name"|
|app|Details URL|-|url||-|-|ouya://launcher/details?app=evil.corptron.DuckGame||
|app|Game website|website (optional)||website|-|App.website|http://www.winterworks.de||
|app|Content rating|contentRating (optional)|contentRating|contentRating|suggestedAge|App.contentRating|Everyone|Everyone<br /> 9+<br /> 12+<br /> 17+|
|app|FIXME|premium (optional)|premium|premium|premium|App.premium|false||
|app|When the game was published|firstPublishedAt (optional)||firstPublishedAt|firstPublishedAt (unix timestamp)|App.firstPublishedAt|2015-10-09T07:53:25Z||
|app|Are there in-app purchases?|inAppPurchases (optional)|inAppPurchases||inAppPurchases|-|true||
|app|FIXME|-|type||type|-|app|discover: "app", "discover" or "details_page" (for bundles)<br /> details: "Game", FIXME|
|app|Last update (unix timestamp)|-|updated_at||-|AppVersion.releaseTime|1417731390||
|app|Last update|releases.*.date (when "latest")|updatedAt||-|AppVersion.releaseTime|2014-12-04T22:16:30Z||
|?|Metadata about the file|-|-||metaData|-|["key:rating.average", "key:developer.name", "key:suggestedAge", "45.29 MiB"]|Always those 4 values and in the same order (at least for apps)|
|rating|Number of likes?|rating.likeCount (optional)||likeCount|-|Rating.likeCount|0||
|rating|Average rating|rating.average (optional)|rating.average|ratingAverage|rating.average|Rating.rating|4.1||
|rating|Number of ratings|rating.count (optional)|rating.count|ratingCount|rating.count|Rating.reviewCount|355||
|product|Promotion data|products.* (when "promoted=true")|promotedProduct|promotedProduct|promotedProduct|App.promotedProduct|null|May be "null" if none, otherwise object|
|product|Product key|products.*.identifier|promotedProduct.identifier|promotedProduct.identifier|promotedProduct.identifier|Product.identifier|unlock_rockets||
|product|Product name|products.*.name|promotedProduct.name|promotedProduct.name|promotedProduct.name|Product.name|Unlock Full Game||
|product|Product currency|products.*.currency|promotedProduct.currency|promotedProduct.currency|promotedProduct.currency|-|EUR||
|product|Product description|products.*.description|promotedProduct.description|promotedProduct.description|promotedProduct.description|Product.description|Remove the 9 satellites limitation. Infinite satellites!||
|product|Saving|-|promotedProduct.percentOff|promotedProduct.percentOff|promotedProduct.percentOff|-|0||
|product|Current price|products.*.localPrice|promotedProduct.localPrice|promotedProduct.localPrice|promotedProduct.localPrice|-|1.99||
|product|Previous price|products.*.originalPrice|promotedProduct.originalPrice|promotedProduct.originalPrice|promotedProduct.originalPrice|Product.originalPrice|1.99||
|product|FIXME|?|?|?|promotedProduct.type|?|"entitlement" (502x), null (758x)||
|apk|Human readable version|releases.*.name|latestVersion.versionNumber|versionNumber|version.number|Apk.versionName|1.6|Run "aapt dump badging file.apk", field "versionName"|
|apk|UUID of latest apk version|releases.*.uuid|uuid<br /> latestVersion.uuid|uuid<br /> latestVersion|version.uuid|App.uuid|780688a9-95ee-429a-8755-69a8d0c88fe0|The OUYA API does not have app UUIDs, only release/apk uuids.|
|apk|Internal version|releases.*.versionCode|-|-|apk.versionCode|Apk.versionCode|null, 120401, 11, 1001004|Run "aapt dump badging file.apk", field "versionCode"|
|apk|FIXME|releases.*.publicSize (optional)||publicSize|apk.publicSize|Apk.publicSize|27275||
|apk|FIXME|releases.*.nativeSize (optional)||nativeSize|apk.nativeSize|Apk.nativeSize|20292||
|apk|MD5 file hash|releases.*.md5sum|latestVersion.apk.md5sum|md5sum|apk.md5sum|Apk.md5sum|a5b0f82d54df5f551a64295e43771a10||
|apk|APK file size|releases.*.size||apkFileSize|apk.fileSize|Apk.size|25507828||
|apk|APK publish date|releases.*.date||publishedAt|version.publishedAt (unix timestamp)|AppVersion.releaseTime|2015-10-23T09:58:19Z||
|apk|downloadLink|releases.*.url|||apk.filename|Apk.location|url: https://devs-ouya-tv-prod.s3.amazonaws.com/apps/5a3fbb4d-852b-4af4-becc-324dce6a3b42/de.eiswuxe.blookid2/780688a9-95ee-429a-8755-69a8d0c88fe0/lFzMjcZyQauvWX5k8HvH_blookid2.apk<br /> details: 1zbYKRSS1elKIYI9eseH_BombSquad-ouya-release.apk|Also in download.json<br /> Details field: only a file name, no path/domain. Always set.|
|apk|state|-|?|?|apk.state|?|"complete"||
|media|Discover image|media.discover|image||tileImage|Media|https://www.filepicker.io/api/file/05y2T8cKTY6cUfX7RYFR||
|media|Large image|media.large||mainImageFullUrl|-|App.titleImage|https://d3e4aumcqn8cw3.cloudfront.net/api/file/MASaiOBlTEO7GKYXsIns||
|media|FIXME|-|-|-|heroImage.url|?|https://s3.amazonaws.com/ouya-screenshots/3d819f4e-2195-433c-81ea-c766a6f3144f/ouya-image20170118-3-i56ad1<br /> null|Most games, had "null" here, only 14 had one set.<br /> When this was set, mobileAppIcon was also set.|
|media|Video|media.video (optional)||videoUrl||Media|https://vimeo.com/141878938||
|media|Game screenshots|media.screenshots (optional)||filepickerScreenshots|-|Media|[urls]||
|media|Details images+videos|media.details (optional)|||mediaTiles|?|[<br /> {<br /> "type: "image",<br /> "urls": [<br /> "full": "http://...",<br /> "thumb": "http://...",<br /> ],<br /> "fp_url": "http://...."<br /> },<br /> {<br /> "type": "video",<br /> "url": "https://vimeo.com/141878938"<br /> }<br />]|Allows free ordering of images and videos on the details page.<br /> "fp" is probably "FilePicker".<br /> fp_url is not used in OUYA's launcher.<br /> The launcher loads the thumbnail at first, immediately after the full image.<br /> If no mediaTile is available, the app's mainImageFullUrl<br /> is used. When offline, the apk image is used.<br /> Game data information:<br /> If details is not given or an emtpy array,<br /> "mediaTile" array should automatically be created by combining<br /> "media.large", "media.video" and "media.screenshots" into the<br /> mediaTiles format.||media|FIXME|-||mobileAppIcon|mobileAppIcon|App.iconImage|null<br /> https://s3.amazonaws.com/ouya-screenshots/3ae8fc67-f7f5-4f97-a48c-8ea2d31460fd/ouya-image20170911-3-v6tjz1|1246x null, rest had its own URL.<br /> Set when heroImage was filled.|
|developer|Developer name|developer.name||developer|developer.name|Developer.name|winterworks GmbH||
|developer|Developer support mail|developer.supportEmail (optional)||supportEmailAddress|-|Developer.supportEmail|null||
|developer|Support phone number|developer.supportPhone (optional)||supportPhone|-|Developer.supportPhone|null||
|developer|Developer is a founder|developer.founder (optional)||founder|developer.founder|Developer.founder|false||
|developer|Developer UUID|developer.uuid (optional)|-|-|developer.url (part of)|Developer.uuid|ouya://launcher/details?developer=5b015434-8a78-4274-aa5d-0cb2e330e50e||
