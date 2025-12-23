#!/bin/bash

DIR=$(cd $(dirname $0) && pwd)

echo "uname = $(uname)"
echo "DIR = ${DIR}"

if [[ "$(uname)" == "Darwin" ]]; then
    cmd="/opt/homebrew/bin/php"
else
    cmd="/usr/bin/php"
fi

arr=(
    "expect_gokubushin.php"
    "expect_hyakuren.php"
    "expect_pickup6.php"
    "expect_re_pickup.php"
    "expect_rankup5.php"
    "expect_rankup6.php"
    "expect_sr_pickup.php"
    "pull_gokubushin.php"
    "pull_hyakuren.php"
    "pull_hyakuren2.php"
    "pull_pickup6.php"
    "pull_re_pickup.php"
    "pull_rankup5.php"
    "pull_rankup6.php"
    "pull_sr_pickup.php"
)

for name in ${arr[@]}
do
    echo "[execute] " $name
    $cmd ${DIR}/${name} 1>/dev/null
done
