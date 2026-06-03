<?php
/**
 * Title: single-interview
 * Slug: jong-literair-nederland/single-interview
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"margin":{"top":"0rem"}}},"backgroundColor":"jln-orange","layout":{"type":"constrained"}} -->
<main class="wp-block-group has-jln-orange-background-color has-background" style="margin-top:0rem"><!-- wp:columns {"align":"wide","backgroundColor":"white"} -->
<div class="wp-block-columns alignwide has-white-background-color has-background"><!-- wp:column {"width":"75%"} -->
<div class="wp-block-column" style="flex-basis:75%"><!-- wp:jln/jln-titel /-->

<!-- wp:post-content {"layout":{"type":"default"}} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"25%"} -->
<div class="wp-block-column" style="flex-basis:25%"><!-- wp:query {"queryId":1,"query":{"postType":"post","perPage":10,"pages":1,"order":"desc","orderBy":"date","queryType":"","offset":0,"exclude":[],"inherit":false},"namespace":"ln-query","align":["wide","full"]} -->
<div class="wp-block-query"><!-- wp:group {"metadata":{"categories":[],"patternName":"core/block/12102","name":"JLN Heading"},"style":{"spacing":{"margin":{"bottom":"2em"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="margin-bottom:2em"><!-- wp:heading {"level":3,"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"typography":{"textTransform":"uppercase","lineHeight":"1","letterSpacing":"2px"},"spacing":{"margin":{"right":"1rem","left":"1rem"},"padding":{"top":"0.5rem","bottom":"0.5rem","left":"1rem","right":"1rem"}}},"backgroundColor":"teal","textColor":"white"} -->
<h3 class="wp-block-heading has-white-color has-teal-background-color has-text-color has-background has-link-color" style="margin-right:1rem;margin-left:1rem;padding-top:0.5rem;padding-right:1rem;padding-bottom:0.5rem;padding-left:1rem;letter-spacing:2px;line-height:1;text-transform:uppercase"><?php esc_html_e('Recent', 'jong-literair-nederland');?></h3>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:post-template {"layout":{"type":"grid","columnCount":1}} -->
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group"><!-- wp:post-featured-image {"style":{"layout":{"selfStretch":"fixed","flexSize":"6em"}}} /-->

<!-- wp:jln/jln-titel {"titleLevel":"h4"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->