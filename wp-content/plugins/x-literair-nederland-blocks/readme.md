#To add a new block

cd literair-nederland-blocks

npx @wordpress/create-block ln-donation ^
  --namespace=ln ^
  --title "LN Donation" ^
  --short-description "Donation embed block" ^
  --category=widgets ^
  --variant=dynamic ^
  --no-plugin ^
  --target-dir ./blocks/ln-donation



npx @wordpress/create-block ln-fotosynthese ^
  --namespace=ln ^
  --title "LN Fotosynthese" ^
  --short-description "Block for background image" ^
  --category=literair-nederland ^
  --variant=dynamic ^
  --no-plugin ^
  --target-dir ./blocks/ln-fotosynthese


# Debug auto-insert bibliographics

- The editor auto-insert flow logs only when debug is enabled.
- As admin, open the editor URL with `lnAutoInsertDebug=1`, for example:
  - `post.php?post=123&action=edit&lnAutoInsertDebug=1`
- Open the browser console and look for logs prefixed with `[LN AutoInsert Bibliographics]`.

Enable debug permanently through a filter (for local/dev only):

```php
add_filter( 'ln_auto_insert_bibliographics_debug', '__return_true' );
```
