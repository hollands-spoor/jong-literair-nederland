<?php
/**
 * Title: home
 * Slug: jong-literair-nederland/home
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"margin":{"top":"0rem"}}},"backgroundColor":"jln-orange","layout":{"type":"constrained"}} -->
<main class="wp-block-group has-jln-orange-background-color has-background" style="margin-top:0rem"><!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"top":"0em","bottom":"0em"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-white-background-color has-background" style="margin-top:0em;margin-bottom:0em"><!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"top":"0em","bottom":"0em"}}}} -->
<div class="wp-block-columns alignwide" style="margin-top:0em;margin-bottom:0em"><!-- wp:column {"width":"75%"} -->
<div class="wp-block-column" style="flex-basis:75%"><!-- wp:query {"queryId":1,"query":{"postType":"post","perPage":4,"pages":1,"order":"desc","orderBy":"date","queryType":"","offset":0,"exclude":[],"inherit":false},"namespace":"ln-query","align":"wide","className":"mpt"} -->
<div class="wp-block-query alignwide mpt"><!-- wp:post-template {"style":{"spacing":{"blockGap":"3em"}},"layout":{"type":"grid","columnCount":2}} -->
<!-- wp:jln/jln-titel {"titleLevel":"h2"} /-->

<!-- wp:post-featured-image /-->

<!-- wp:post-excerpt {"moreText":"<?php esc_attr_e('Lees verder', 'jong-literair-nederland');?>"} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"25%"} -->
<div class="wp-block-column" style="flex-basis:25%"><!-- wp:query {"queryId":6,"query":{"postType":"post","perPage":1,"pages":1,"order":"desc","orderBy":"date","queryType":"","offset":0,"exclude":[],"inherit":false,"taxQuery":{"include":{"category":[34]}}},"namespace":"ln-query"} -->
<div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":1}} -->
<!-- wp:jln/jln-titel {"titleLevel":"h2","showDate":false,"showBoekInfo":false} /-->

<!-- wp:post-excerpt {"moreText":"<?php esc_attr_e('Lees verder<br>', 'jong-literair-nederland');?>","excerptLength":100} /-->

<!-- wp:post-featured-image /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"top":"0px","bottom":"0px"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-white-background-color has-background" style="margin-top:0px;margin-bottom:0px"><!-- wp:columns {"align":"full"} -->
<div class="wp-block-columns alignfull"><!-- wp:column {"width":"75%"} -->
<div class="wp-block-column" style="flex-basis:75%"><!-- wp:query {"queryId":3,"query":{"postType":"post","perPage":10,"pages":1,"order":"desc","orderBy":"date","queryType":"","offset":0,"exclude":[],"inherit":false},"namespace":"ln-query"} -->
<div class="wp-block-query"><!-- wp:group {"metadata":{"categories":[],"patternName":"core/block/12102","name":"JLN Heading"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":3,"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"typography":{"textTransform":"uppercase","lineHeight":"1","letterSpacing":"2px"},"spacing":{"margin":{"right":"1rem","left":"1rem"},"padding":{"top":"0.5rem","bottom":"0.5rem","left":"1rem","right":"1rem"}}},"backgroundColor":"teal","textColor":"white"} -->
<h3 class="wp-block-heading has-white-color has-teal-background-color has-text-color has-background has-link-color" style="margin-right:1rem;margin-left:1rem;padding-top:0.5rem;padding-right:1rem;padding-bottom:0.5rem;padding-left:1rem;letter-spacing:2px;line-height:1;text-transform:uppercase"><?php esc_html_e('Meer Berichten', 'jong-literair-nederland');?></h3>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:post-template {"style":{"spacing":{"blockGap":"1em"}},"layout":{"type":"grid","columnCount":1}} -->
<!-- wp:columns {"style":{"spacing":{"margin":{"top":"0.5em","bottom":"0.5em"}}}} -->
<div class="wp-block-columns" style="margin-top:0.5em;margin-bottom:0.5em"><!-- wp:column {"width":"25%"} -->
<div class="wp-block-column" style="flex-basis:25%"></div>
<!-- /wp:column -->

<!-- wp:column {"width":"75%"} -->
<div class="wp-block-column" style="flex-basis:75%"><!-- wp:jln/jln-titel {"titleLevel":"h3"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"25%"} -->
<div class="wp-block-column" style="flex-basis:25%"><!-- wp:post-featured-image /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"75%"} -->
<div class="wp-block-column" style="flex-basis:75%"><!-- wp:post-excerpt /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"25%"} -->
<div class="wp-block-column" style="flex-basis:25%"><!-- wp:query {"queryId":16,"query":{"postType":"post","perPage":48,"pages":1,"order":"desc","orderBy":"date","queryType":"","offset":0,"exclude":[],"inherit":false,"taxQuery":{"include":{"category":[33,62,32]}}},"namespace":"ln-query","className":"jln-omslagen"} -->
<div class="wp-block-query jln-omslagen"><!-- wp:group {"metadata":{"categories":[],"patternName":"core/block/12102","name":"JLN Heading"},"style":{"spacing":{"margin":{"bottom":"2em"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="margin-bottom:2em"><!-- wp:heading {"level":3,"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"typography":{"textTransform":"uppercase","lineHeight":"1","letterSpacing":"2px"},"spacing":{"margin":{"right":"1rem","left":"1rem"},"padding":{"top":"0.5rem","bottom":"0.5rem","left":"1rem","right":"1rem"}}},"backgroundColor":"teal","textColor":"white"} -->
<h3 class="wp-block-heading has-white-color has-teal-background-color has-text-color has-background has-link-color" style="margin-right:1rem;margin-left:1rem;padding-top:0.5rem;padding-right:1rem;padding-bottom:0.5rem;padding-left:1rem;letter-spacing:2px;line-height:1;text-transform:uppercase"><?php esc_html_e('Omslagen', 'jong-literair-nederland');?></h3>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:post-template {"className":"no-dividers ln-padding","layout":{"type":"grid","columnCount":4,"minimumColumnWidth":null}} -->
<!-- wp:post-featured-image {"isLink":true,"style":{"border":{"radius":{"topLeft":"2px","topRight":"2px","bottomLeft":"2px","bottomRight":"2px"}},"spacing":{"margin":{"right":"0","left":"0"}}}} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"top":"0em"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-white-background-color has-background" style="margin-top:0em"><!-- wp:group {"metadata":{"categories":[],"patternName":"core/block/12102","name":"JLN Heading"},"align":"wide","style":{"spacing":{"padding":{"top":"0px","bottom":"0px","left":"0px","right":"0px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide" style="padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px"><!-- wp:heading {"level":3,"align":"wide","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"typography":{"textTransform":"uppercase","lineHeight":"1","letterSpacing":"2px"},"spacing":{"margin":{"right":"1rem","left":"1rem"},"padding":{"top":"0.5rem","bottom":"0.5rem","left":"1rem","right":"1rem"}}},"backgroundColor":"teal","textColor":"white"} -->
<h3 class="wp-block-heading alignwide has-white-color has-teal-background-color has-text-color has-background has-link-color" style="margin-right:1rem;margin-left:1rem;padding-top:0.5rem;padding-right:1rem;padding-bottom:0.5rem;padding-left:1rem;letter-spacing:2px;line-height:1;text-transform:uppercase"><?php esc_html_e('Veelgebruikte Tags', 'jong-literair-nederland');?></h3>
<!-- /wp:heading -->

<!-- wp:tag-cloud {"numberOfTags":100,"smallestFontSize":"0.75em","largestFontSize":"2em","align":"wide","className":"is-style-default"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->