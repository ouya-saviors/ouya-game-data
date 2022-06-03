**************
OUYA game data
**************
.. image:: https://travis-ci.com/ouya-saviors/ouya-game-data.svg?branch=master
    :target: https://travis-ci.com/ouya-saviors/ouya-game-data

Meta data of games for the OUYA console.
Use them to populate your own "Discover" server.

Game ``.apk`` files are hosted on `The OUYA library`__ in the Internet Archive.

__ https://archive.org/details/ouyalibrary


How to help
===========

Add a new version
-----------------

1. Locate the JSON game data file in the ``classic`` folder
2. Open it in a text editor
3. Go to ``"releases"`` and duplicate the current release,
   then adjust it:

   1. Update ``name`` to the new version
   2. To get ``versionCode`` you need to have the Android SDK tool
      ``aapt`` or ``aapt2``::

       $ aapt dump badging game.apk
       package: name='example.game' versionCode='23' versionName='1.23'

   3. Create a random ``uuid`` with a web service or the ``uuid``
      command line tool
   4. Set ``date`` to the time the version has been released
   5. ``latest`` must be ``true`` for the new version, and ``false``
      for all others.
   6. ``url`` must be a HTTP URL, not HTTPS!
   7. ``size`` is the apk file size in bytes
   8. ``md5sum`` can be created with the ``md5sum`` command line tool.

4. Check if everything is correct -> See "validating the game files"

   You can also create a patch/pull request;
   Github will run the checks automatically.


Add a new game/app
------------------
You can use the script ``bin/create-from-apk.php`` to generate the JSON.
Only adjust the FIXME fields afterwards!

Copy ``example-game.json`` to the ``new/`` folder and adjust it.
You can get ``packageName`` from ``aapt`` as described above.

Note that all URLs have to be plain ``http``, HTTPS is not supported!

Use a random ``uuid`` as developer UUID.
Use ``0`` for the first 8 characters so we can distinguish original UUIDs
from self-generated ones.

Extract image
.............
On the command line::

  $ unzip -j game.apk res/drawable-xhdpi-v4/ouya_icon.png

This image has a size of 732x412 pixels.

Media images need to have a full size of 1280x720,
while thumbnails have a size of 852x479.


Games in demo mode
==================
When you start a game, it should be unlocked (full version) automatically.

If a game is stuck in demo mode despite that you installed the
`ouya-plain-purchases`__ module, this can have the following reasons:

1. The game data file has no purchasable products (most often)
2. The developer UUID is wrong. `Fix it!`__ (sometimes)
3. The game does not fetch receipts from the server when starting up
   (seldom)
4. We have no game data file for it (seldom)

__ http://cweiske.de/tagebuch/ouya-purchases.htm
__ https://github.com/ouya-saviors/ouya-game-data/issues/14



Developer UUIDs
---------------
We do not have all developer UUIDs which are required for in-game purchases
to work.
Dummy UUIDs begin with ``00000000``.
UUIDs that are not important because the game has no IAPs begin with ``11111111``.


Adding products
---------------
When there are no products in the game data file, we have to get them from
the game itself.

1. Add a new line ``DEBUG=1`` in the ``ouya_config.properties`` file and reboot.
2. Connect the OUYA to your PC and run ``adb logcat``.
3. Start the game and look at the logcat output

There will be lines like this::

  D/HTTP    (  604): Request 33: GET /api/v1/developers/b8b9eb6d-.../products/?auth_token=...&only=overkill2_om_1%2Coverkill2_om_2

Everything after ``&only=`` are product IDs.
``%2C`` is an URL-encoded comma, so in the example we have two product IDs.

Now have a look at the ``example-game.json`` file and add new products to
your game's data file (in the ``classic/`` folder).

If you do not know what price the product had, use ``0.01``.


Validating the game files
=========================
We provide a JSON schema file: ``ouya-game.schema.json``.


Setup
-----
- Install php-json-schema__ to get the ``validate-json`` cli tool
- Use python's ``pip3`` to install the jsonschema__ cli tool

__ https://github.com/justinrainbow/json-schema
__ https://github.com/Julian/jsonschema


Links
=====
Self-hosted OUYA servers:

- https://github.com/cweiske/stouyapi/
- https://gitlab.com/devirich/BrewyaOnOuya
- https://github.com/cweiske/louyapi/
