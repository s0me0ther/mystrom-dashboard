#!/usr/bin/env bash

# stop if any command fails
set -e

ROOTDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
echo "Determinated [$ROOTDIR] as project root directory"
echo ""

printf "Are you sure to reset the repository data and \e[31mdelete all local data?\e[0m [yes/No]: \n"
read -r c
if [ "$c" != "yes" ]; then
    echo "Abort"
    exit
fi

rm -rf $ROOTDIR/app/vendor
rm -rf $ROOTDIR/data/app/solarstats.db
rm -rf $ROOTDIR/data/grafana/lib/csv
rm -rf $ROOTDIR/data/grafana/lib/pdf
rm -rf $ROOTDIR/data/grafana/lib/plugins
rm -rf $ROOTDIR/data/grafana/lib/png
rm -rf $ROOTDIR/data/grafana/lib/grafana.db

echo "Done"
