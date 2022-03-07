#!/usr/bin/env bash
FONT_LOCATION=~/Google\ Drive/My\ Drive/03-CRF/RedCrossQuest/Paid\ Stuff/Glyphicons\ Pro/glyphicons_pro_1_9_2/glyphicons/web/bootstrap_example/fonts/
rm -fr /tmp/glyphiconspro

mkdir /tmp/glyphiconspro
cd /tmp/glyphiconspro || exit
git clone https://github.com/dev-mansonthomas/bootstrap3-glyphicons-pro.git
cp bootstrap3-glyphicons-pro/src/bootstrap-sass/assets/stylesheets/_bootstrap.scss                $OLDPWD/bower_components/bootstrap-sass/assets/stylesheets/
cp bootstrap3-glyphicons-pro/src/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss      $OLDPWD/bower_components/bootstrap-sass/assets/stylesheets/bootstrap/
cp bootstrap3-glyphicons-pro/src/bootstrap-sass/assets/stylesheets/bootstrap/_glyphicons-pro.scss $OLDPWD/bower_components/bootstrap-sass/assets/stylesheets/bootstrap/

cd - || exit
cd "$FONT_LOCATION" || exit

cp ./*  $OLDPWD/bower_components/bootstrap-sass/assets/fonts/bootstrap/

cd - || exit
