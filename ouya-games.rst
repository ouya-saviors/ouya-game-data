**********************************
OUYA games in the Internet Archive
**********************************

https://archive.org/details/ouyalibrary

Fetch all items in the collection::

  $ ia search collection:ouyalibrary | jq -r .identifier > ia-packages

Fetch data/files for one item::

  $ curl -s https://archive.org/metadata/ouya_de.eiswuxe.blookid2_1.6|jq .

Extract discover images::

  $ cat discover.json | jq 'reduce (.tiles[]| select(.package!=null)) as $i ({}; .[$i.package] = $i.image)'

(We simply use a scaled version of the main image as discover image)
