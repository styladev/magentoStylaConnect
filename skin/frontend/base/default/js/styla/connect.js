window.stylaUpdateCart = function stylaUpdateCart(data) {
    if (!jQuery) {
        return;
    }
    /**
     * This function is called after a user successfully adds a new product to his cart, using the styla product story page.
     *
     * It's used to load updated minicart content and (possibly) open the minicart window, if possible.
     * You can rewrite this to suit your needs better.
     */

    /**
     * Look for the default minicart block, and if it's found - update it's content with the data we got from this event
     */
    var $minicartContentElement = jQuery("#header-cart");
    if ($minicartContentElement && data.html !== undefined && data.html.minicart_content !== undefined) {
        $minicartContentElement.html(data.html.minicart_content);
    }

    /**
     * The default theme (rwd/default) also has a numeric label next to the minicart - we can update it using the raw
     * cart "metadata".
     */
    var $cartCountElement = jQuery(".skip-cart .count");
    if ($cartCountElement && data.hasOwnProperty('meta')) {
        $cartCountElement.html(data.meta.num_items);
    }
};