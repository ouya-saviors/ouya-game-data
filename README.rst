**************
OUYA game data
**************
.. image:: https://travis-ci.com/ouya-saviors/ouya-game-data.svg?branch=master
    :target: https://travis-ci.com/ouya-saviors/ouya-game-data

Meta data of games for the OUYA console.
Use them to populate your own "Discover" server.



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

4. Check if everything is correct with::

     ./bin/validate-game.sh classic/$gamefile.json

   This needs the ``validate-json`` and ``jsonschema`` commands that
   are not that easy to obtain.

   You can also create a patch/pull request;
   Github will run the checks automatically.


Add a new game/app
------------------

Copy ``example-game.json`` to the ``new/`` folder and adjust it.
You can get ``packageName`` from ``aapt`` as described above.

Note that all URLs have to be plain ``http``, HTTPS is not supported!

Use a random ``uuid`` as developer UUID.



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
