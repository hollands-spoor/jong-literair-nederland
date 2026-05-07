# Shared Plugin Submodule Checklist

This repository uses x-literair-nederland-blocks as a Git submodule.

## 1) First clone

Run:

git clone --recurse-submodules https://github.com/hollands-spoor/jong-literair-nederland.git

If already cloned without submodules:

git submodule update --init --recursive

## 2) Daily pull

Run:

git pull
git submodule update --init --recursive

## 3) Develop shared plugin

Do shared plugin changes in the plugin repository:

https://github.com/hollands-spoor/x-literair-nederland-blocks

Commit and push there first.

## 4) Bump plugin version in this site

From this repository root:

git submodule update --remote --merge wp-content/plugins/x-literair-nederland-blocks
git add wp-content/plugins/x-literair-nederland-blocks
git commit -m "Bump x-literair-nederland-blocks submodule"
git push

## 5) Check pinned plugin commit

Run:

git submodule status

The line for wp-content/plugins/x-literair-nederland-blocks shows the exact pinned plugin commit.

## 6) Recover from detached HEAD in submodule

Run:

cd wp-content/plugins/x-literair-nederland-blocks
git checkout main
git pull
cd ../../..

Then commit the updated submodule pointer in this repo if needed.
