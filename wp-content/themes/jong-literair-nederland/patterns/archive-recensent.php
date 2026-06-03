<?php
/**
 * Title: archive-recensent
 * Slug: jong-literair-nederland/archive-recensent
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:group {"tagName":"main","backgroundColor":"jln-orange","layout":{"type":"constrained"}} -->
<main class="wp-block-group has-jln-orange-background-color has-background"><!-- wp:group {"align":"wide","backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-white-background-color has-background"><!-- wp:heading {"level":1,"align":"wide"} -->
<h1 class="wp-block-heading alignwide"><?php esc_html_e('Recensenten en Columnisten', 'jong-literair-nederland');?></h1>
<!-- /wp:heading -->

<!-- wp:query {"queryId":15,"query":{"perPage":100,"pages":0,"offset":0,"postType":"recensent","order":"asc","orderBy":"title","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":null,"parents":[],"format":[]},"align":"wide"} -->
<div class="wp-block-query alignwide"><!-- wp:post-template {"layout":{"type":"grid","columnCount":4}} -->
<!-- wp:jln/jln-titel {"titleLevel":"h2","showDate":false,"showBoekInfo":false,"showRecensent":false} /-->

<!-- wp:post-featured-image /-->

<!-- wp:post-excerpt {"moreText":"<?php esc_attr_e('Lees verder', 'jong-literair-nederland');?>","excerptLength":100} /-->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"align":"center","layout":{"type":"flex","justifyContent":"space-between"}} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-numbers /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"placeholder":"Tekst of blokken toevoegen die worden getoond wanneer de query geen resultaten oplevert."} -->
<p></p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:group --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->