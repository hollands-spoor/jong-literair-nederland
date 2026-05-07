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

## 7) Release routine (recommended: tagged)

Use this each time you release plugin changes.

### A. In plugin repo (x-literair-nederland-blocks)

1) Bump plugin version in plugin main file.
2) Commit and push.
3) Tag and push tag.

Commands:

git add .
git commit -m "Release vX.Y.Z"
git push
git tag vX.Y.Z
git push origin vX.Y.Z

### B. In this site repo (jong-literair-nederland)

1) Pull latest main.
2) Update submodule pointer to latest plugin commit.
3) Commit pointer bump and push.

Commands:

git pull
git submodule update --remote --merge wp-content/plugins/x-literair-nederland-blocks
git add wp-content/plugins/x-literair-nederland-blocks
git commit -m "Bump x-literair-nederland-blocks to vX.Y.Z"
git push

### C. In literairnederland repo

Repeat section B there as well, then deploy/test each site.


For fast update/merger of x-ln plugin: 

## 2) Update site repo pointers

Do this in each site repo root:

git pull
git submodule update --remote --merge wp-content/plugins/x-literair-nederland-blocks
git add wp-content/plugins/x-literair-nederland-blocks
git commit -m "Bump x-literair-nederland-blocks to vX.Y.Z"
git push

## 3) Verify pinned commit in each site

Run:

git submodule status

The line for wp-content/plugins/x-literair-nederland-blocks should point to the expected plugin commit/tag release.

## 4) Rollback

If a release must be reverted on a site, checkout the previous site commit that bumped the submodule pointer, or reset pointer to the previous known-good plugin commit and recommit.
