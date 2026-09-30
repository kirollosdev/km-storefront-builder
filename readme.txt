=== KM Storefront Builder ===
Contributors: Kirollos Magdy
Author URI: https://kirollosmagdy.com
Plugin URI: https://github.com/kirollosdev/km-storefront-builder
Tags: woocommerce, xstore, variation swatches, filters
Requires at least: 5.9
Requires PHP: 7.4
Stable tag: 2.12.0
License: Proprietary, all rights reserved

Store tweaks for the XStore theme in one plugin.

== Description ==

= Variation swatches =
* Single product page and XStore's quick view popup. Shop filters and other forms keep their normal dropdowns.
* Color attributes show as circles filled with the real color; every other attribute (size, material, ...) shows as boxes.
* The color is detected from the term name: CSS color names, fashion names (Navy, Burgundy, Rose Gold, Off White, Charcoal, ...) and Arabic names (احمر، كحلي، بيج، نبيتي، هافان، اوف وايت، ...).
* "Light Blue", "Dark Green", "ازرق فاتح", "اخضر غامق" become lighter or darker shades. "Black / White" becomes a split circle, "Multicolor" / "ملون" a rainbow. A hex code in the name is used directly.
* An attribute counts as a color when its name contains Color, Colour or لون, or when every value is a known color.
* Unavailable combinations are crossed out, clicking a selected swatch clears it, and the chosen value is shown next to the label ("Color: Navy").
* Products > Attributes > Configure terms: optional color picker per term, and a Swatch column showing the detected color marked "auto".

= Hide Uncategorized =
* Removes WooCommerce's default "Uncategorized" category from shop filters, category widgets and category lists on the front end. Admin screens are untouched.

= Filter arrows =
* XStore's collapsible sidebar filters show an up arrow when open and a down arrow when collapsed, instead of − and +.

= Product card buttons =
* Works on every XStore product card across the site (shop and category grids, related products), except the homepage unless Store tools > Card buttons on the homepage is ticked. Cards whose widget shows no cart button in the image area get one added automatically (filter kmst_loop_buttons_in_widget_cards to turn that off).
* Replaces "Select options" on product cards.
* Products with a color option: "Quick view", which opens XStore's quick view popup.
* Products with only a size option: "Add to cart". Tapping it shows the sizes (a popover on desktop, a bottom sheet on phones); tapping a size adds it to the cart by AJAX and refreshes the mini cart. Sold-out sizes are crossed out.
* The button sits centered inside the product image: white background with no border, 10px rounded corners, black text; primary background with white text and icon on hover (XStore's black Quick View bar and the button under the price are replaced). Always visible on touch screens. On desktop it shows on hover, or all the time with WooCommerce > Settings > Products > Store tools > Card button visibility > Always visible.
* Card color swatches: products with a color option show color dots on the card (inside the image above the button, or below the image). Choosing a color swaps the card photo to that color's variation photo in the same image box, and the product link opens with that color selected. Desktop hover previews a color. Up to 5 dots, then +N. Setting: Store tools > Card color swatches.
* Simple products get the same "Add to cart" bar and keep WooCommerce's normal add to cart.
* Bar colors can be overridden with the CSS variables --kmst-lb-bar-bg, --kmst-lb-bar-color and --kmst-lb-bar-border (plus -hover versions) --kmst-lb-bar-radius and --kmst-lb-bar-border-width. Products with two non-color options (for example size and cup) use Quick view.
* Filter `kmst_swatches_quick_view_actions` adds AJAX actions of other quick view plugins.
* Filter `kmst_loop_button_mode` returns quickview, sizes or an empty string to force a button type per product.

= Product card extras =
* Hover photo: on product cards everywhere (shop, carousels, related products, homepage), hovering slides in the product's next photo (its first gallery image) from the right. Desktop only; the image box keeps its size. Setting: Store tools > Card hover photo.
* Product labels (Sale, New, Hot...) get 10px rounded corners; circle-style labels stay circles. Setting: Store tools > Rounded product labels. CSS variable --kmst-badge-radius.
* Wishlist heart on product cards sits on a white circle at 70% opacity and appears on hover (Store tools > Wishlist icon always visible shows it all the time). Setting: Store tools > Wishlist icon circle. CSS variables --kmst-wishlist-size and --kmst-wishlist-opacity.

= Polylang shop languages =
* For free Polylang (language in the URL directory, e.g. /ar/). Polylang normally redirects /ar/shop/ to /shop/ because it does not translate WooCommerce.
* Adds /{lang}/shop/, /{lang}/product-category/.../, /{lang}/product-tag/.../ and /{lang}/product/.../ (with pagination), plus /{lang}/cart/, /{lang}/checkout/ (and order-received), /{lang}/my-account/ with all its sections, and wishlist / compare / order tracking pages (XStore's wishlist at my-account/?et-wishlist-page included). Pages you've already translated in Polylang are left alone. Filter kmst_pll_shop_pages adds more page IDs. Stops the redirect, keeps store links in the current language, and makes the language switcher link to the same store page in the other language.
* The page is then in that language, so Polylang-linked Elementor templates (e.g. an Arabic Products Archive via Connect Polylang for Elementor), menus and RTL are used. Products stay one shared set.
* Turns itself off when Polylang for WooCommerce is active. Setting: Store tools > Polylang shop languages.

= Category Slider (Elementor widget) =
* A row of cards you build by hand: image, title underneath, and a link that covers the whole card. The cards are fixed text and images, not real categories, so they can point at a category, a landing page or any URL.
* Optional heading: a title and a subtitle on one side of the row with the arrows on the other side, like XStore's own category carousel. Title tag, typography and colors are settings; on phones the arrows drop under the heading.
* Cards per view accepts halves (4.5), so a part of the next card shows at the edge and it reads as a slider.
* Hovering zooms the image in inside its box; the card keeps its size (zoom amount and speed are settings, 1 turns it off).
* Slides with arrows, by swiping on touch screens, and by dragging with the mouse. Arrows sit in the heading row or float on the sides of the cards, and grey out at the ends.
* Cards per view, spacing, image height, image fit (contain or cover), image background, padding, corner radius, border, shadow, title typography and colors, and arrow colors and sizes are all Elementor controls, each one responsive.
* Works right to left: in Arabic the row starts from the right and the arrows flip.

= Cart, checkout and account per language =
* WooCommerce knows one cart, checkout, my account and terms page. With these pages translated in Polylang, WooCommerce uses the one in the visitor's language: links, redirects, is_cart() / is_checkout(), account sections, order received, and the checkout requests themselves (their language comes from the page that sent them).
* WooCommerce > Language pages: a table of the pages per language and a button that creates the missing ones as copies of the original (content, Elementor design and page template), with English slugs (shopping-cart, secure-checkout, account, terms-and-conditions), linked as translations.
* WooCommerce > Settings > Advanced keeps showing the original pages. Pages with a translation are no longer served through the /en/ shop-language rules.
* Turns itself off when Polylang for WooCommerce is active.

= Arabic texts =
* XStore ships no Arabic, so about 150 of its storefront texts are translated here, plus the size chart table and the product template's typed subtitle. Only on Arabic pages (Polylang), never in the admin.
* Two layers: untranslated XStore / WooCommerce / WordPress strings get the Arabic (also for quick view, mini cart and other background requests, using the language of the page that made them), and on full pages any text or aria-label / title / placeholder that is exactly one of the entries is replaced. Scripts, styles and form fields are left alone.
* Store tools > Arabic texts: your own "English = Arabic" lines, which win over the built-in ones; "Text =" with nothing after it switches one off.

= Filter bar =
* A horizontal bar for the shop and category pages: Product Type, every product attribute, and Price on the left; Order by, Share and View on the right.
* Elementor widget "Horizontal Filters" (Product Archive group): drag it above the products, reorder filters, rename labels, and style the bar, dropdowns and chips. Or use the shortcode [kmst_filter_bar], which follows the settings page.
* Color attributes list color dots, other attributes show boxes, Product Type lists categories, Price has a two-handle slider with min/max boxes. Rating lists 5 stars, 4 stars & up, 3 stars & up... (one choice at a time, options with no matching products are hidden), using WooCommerce's rating_filter.
* Attribute options that have no products in the current results are hidden.
* Desktop: dropdowns open on hover as well as on click (widget setting "Open dropdowns on hover"). A dropdown that was clicked, has unapplied choices, or has a price being typed stays open until you click elsewhere.
* Selected filters show as removable chips under the bar with "Clear all".
* View switches the product grid between 2, 3 or 4 columns (1 or 2 on phones) and remembers the choice.
* Share opens the phone's share sheet, or copies the page link on desktop.
* Uses WooCommerce's own filter URLs, so the products widget, pagination and shared links all work.
* Phones: a Filter (count) and Sort by toolbar with share and 1/2 column buttons. Filter opens a bottom sheet with every filter as an expandable section and a pinned Clear all / Show results bar; Sort by opens its own sheet.
* Settings: WooCommerce > Settings > Products > Store tools > Filter bar contents.

== Installation ==

1. Upload the zip in Plugins > Add New > Upload Plugin and activate.
2. Deactivate and delete the older separate plugins if they're installed: KM Variation Swatches, KM Hide Uncategorized, KM Product Grid Gallery. Until then this plugin leaves the matching feature off and shows a notice.
3. In XStore, keep the theme's own variation swatches turned off.
4. Each feature can be switched off in WooCommerce > Settings > Products > Store tools.

== Developer filters ==

* `kmst_color_map` — add or override color names: `$map['my color'] = '#123456';`
* `kmst_attribute_type` — force `'color'` or `'label'` for an attribute.
* `kmst_swatches_enabled` — enable or disable swatches for a given context.

Swatch sizes can be changed with CSS variables on `.kmvs-wrap`: `--kmvs-circle`, `--kmvs-box-height`, `--kmvs-gap`, `--kmvs-ink`, `--kmvs-line`.

== Translations ==

* Arabic included (languages/km-storefront-builder-ar.po / .mo): Quick view, Add to cart, the size picker, and every filter bar text are shown in Arabic on Arabic pages (site or Polylang/TranslatePress language set to Arabic).
* Edit or add translations with Loco Translate, or open the .po file in Poedit.

== Changelog ==

Version numbers carry on from the store-specific plugin this was built from: the same number means the
same code.

= 2.12.0 =
* Renamed from KM Storefront Tools to KM Storefront Builder (folder, main file and text domain are now
  km-storefront-builder). Settings, shortcodes and Elementor widgets keep their names, so nothing on the
  site changes. Activating this version switches the old KM Storefront Tools plugin off; delete it afterwards.

= 2.11.6 =
* Brought up to date with everything added since the first release: Elementor Color Swatches widget, KM
  Icons library, rounded checkout / cart / wishlist / quick view styling, the redesigned quantity
  control, Arabic texts for shipping, payment methods, the privacy and terms lines and the wishlist
  notices, and the Polylang fixes for background requests and the wc-ajax address.
* Arabic for the checkout privacy line and the terms checkbox, with their links kept intact.

= 2.11.5 =
* Color Swatches widget shows the name of the chosen color above the swatches, and no longer the word "Color". It follows the selection, wherever the color is picked.

= 2.11.4 =
* Fix: the swatches in the Color Swatches widget were a few pixels wide and colorless. Their sizes and colors are CSS variables that live on the swatch row, so the copy is now placed inside a row of its own.
* The widget leaves the Add to cart form alone by default; hiding that row is a switch you turn on.
* Fix: the +/- icons disappeared in the cart and mini cart. They are drawn at .7em, and the inherited font size there was under 7px.
* Arabic for "Shipping options will be updated during checkout."

= 2.11.3 =
* Fix: in the mini cart the price got a box around it and the quantity number disappeared. The mini cart puts the control and the price side by side as two .quantity elements, and the field was allowed to shrink to nothing. Only the one holding the +/- is styled now, and the number keeps its width.

= 2.11.2 =
* Mini cart quantity control styled properly. The mini cart nests the markup the other way round than the product page, which is why it came out as a box around the number with the +/- squeezed out of sight.
* Color Swatches widget is safer: it shows a copy of the swatch row and passes clicks to the real one, and it hides the row in the Add to cart form only once the copy is on screen. A widget pointing at an attribute the product does not have now hides nothing.

= 2.11.1 =
* Fix: 2.11.0 caused a critical error. The new widget declared one method without the return type Elementor requires, which PHP refuses outright.

= 2.11.0 =
* New Elementor widget "Color Swatches": place the product swatches anywhere on a product template. It takes over the swatch row from the Add to cart form, so there is still one set of swatches doing the real work, and the form row is hidden.
* Controls for the attribute, the label, alignment, swatch size, spacing and corner radius.

= 2.10.29 =
* The small quantity control in the order review fits its narrow cell again, with both +/- visible.
* The quick view quantity control is a little smaller, to suit the panel.

= 2.10.28 =
* The "clear shopping cart" button on the cart page has rounded corners like the rest.

= 2.10.27 =
* The quantity control in the quick view now matches the product page. The quick view has no inner wrapper around the +/- buttons, so the earlier styling never reached it.

= 2.10.26 =
* Arabic for the "... has been added to the wishlist / compare / cart" notices.
* The "Added on: date" line is hidden on the wishlist.

= 2.10.25 =
* Mini cart panel buttons (View cart, Checkout) and the wishlist panel button have the same rounded corners.
* Fix: a photo opened from inside the quick view appeared behind the popup. Elementor's lightbox sits below the quick view; it is now on top.

= 2.10.24 =
* Add to cart and Buy now on the product page and in the quick view have the same rounded corners as the rest of the store.

= 2.10.23 =
* Fix: color dots now work on product cards inside carousels (related products, upsells, the category slider). A carousel copies its slides to loop, and the copies carried the markup but none of the behaviour.
* Cards that appear while the browser tab is in the background are built too.

= 2.10.22 =
* Fix: pointing at a color on a product card shows that color photo again. The card second photo slides over on hover and was covering it.
* Fix: clicking a color on a card threw a script error, so the color was never actually picked and the product link kept the default color.

= 2.10.21 =
* Wishlist page and wishlist panel buttons have the same rounded corners as the rest of the store.

= 2.10.20 =
* Arabic for the payment methods at checkout: the Tap card title and description, and cash on delivery.
* Gateway descriptions with line breaks or tags are now recognised, so they translate too.

= 2.10.19 =
* Fix: the cart and checkout background address (wc-ajax) now carries the language of the page. It was always the site root, which with the default language served without a prefix is an Arabic address, so the English checkout received an Arabic order box.

= 2.10.18 =
* Arabic on the English cart and checkout: the language of a background request is now also given to Polylang itself (pll_preferred_language) and to the locale filter, not only to determine_locale. Polylang was overriding the earlier fix.

= 2.10.17 =
* Arabic for the shipping texts on the checkout: "Shipping methods", "Shipping", and the notice shown when no shipping option is available.

= 2.10.16 =
* Fix: Arabic texts no longer appear on the English cart and checkout. Parts that WooCommerce redraws in the background now use the language of the page that asked for them, instead of the visitor language cookie.
* New quantity control: rounded, taller, centred value, hover states on +/-, no browser spinner arrows; sizes adjustable in Store tools.
* Currency symbol image keeps one size, sits on the text baseline and never breaks away from the amount.
* Checkout order box corners: covers the XStore order details wrappers as well.

= 2.10.15 =
* Checkout and cart: the order box, address fields, coupon box and buttons have rounded corners, set in Store tools.

= 2.10.14 =
* Quantity box has rounded corners to match the Add to cart button, with the radius set in Store tools.
* Currency symbol image keeps one size and spacing, and never breaks away from the amount.

= 2.10.13 =
* The product search box on the "no products found" page has 10px rounded corners.

= 2.10.12 =
* KM Icons: a "KM Icons" library in the Elementor icon picker (menu, bag, cart, account, search, home, wishlist, filter), and [km_icon name="bag"] for use outside Elementor. Can be switched off in Store tools.

= 2.10.11 =
* Fix: /en/shop/ showed no products and the language switcher went to a translated shop page. The shop is a product archive, not a page, so 2.10.7 should not have treated it as one.

= 2.10.10 =
* Arabic for the "Search products..." field.
* Mobile bottom bar: the account and cart buttons open the page in the language you are browsing, so the bar stays Arabic.

= 2.10.9 =
* Arabic for "View all results" under the live search.

= 2.10.8 =
* Mini cart refreshed in the background (Subtotal and the buttons) is translated too.

= 2.10.7 =
* "Continue shopping" in the cart and "Return to shop" in the mini cart stay in the current language.
* Arabic for "Clear shopping cart" and for the coupon OK button.
* Wishlist side panel in Alexandria.

= 2.10.5 =
* Arabic live search results: the heading, count and everything else in the box use Alexandria.

= 2.10.4 =
* Live search results box is wider than the search field on desktop (up to 620px), so names and prices fit; the heading and count no longer overlap on Arabic pages.

= 2.10.3 =
* Arabic for the search texts: "{{count}} items found" and "Unfortunately, there are no products that match your criteria".
* Live search results: the product name uses the full width instead of wrapping word by word; Alexandria on Arabic pages.

= 2.10.2 =
* Arabic texts: the search box (No results were found!, Products, Pages, Posts, Category: ...).

= 2.10.1 =
* Arabic texts: the wishlist page (Action, Stock status, Ask for an estimate, Share on, bulk select, delete and copy link, and its messages).

= 2.10.0 =
* Fix: XStore's account panel, mobile menu, wishlist and compare links went to the English My account on Arabic pages, because XStore reads the saved page setting directly. The cart, checkout, my account and terms page settings now answer with the page in the visitor's language on the front end (the admin still shows the originals).

= 2.9.9 =
* Arabic texts: the My account dashboard ([woocommerce_my_account] with XStore's dashboard): welcome title and line, Recent orders / Addresses / Account details buttons, the Hello / log out line, the dashboard intro and the account menu labels (used when WooCommerce's own Arabic is missing).

= 2.9.8 =
* Empty cart / compare / wishlist heading uses Alexandria on Arabic pages (the heading font has no Arabic letters).

= 2.9.7 =
* Arabic texts: the empty cart message ("We invite you to get acquainted with an assortment of our shop...") and the empty compare / wishlist headings.

= 2.9.6 =
* Arabic texts: cart and checkout (Your order, Total, Remove, Change address, Shipping to..., coupon, Place order) and the order-received page (Order status, Order number, Date, Email, Total, Payment method, Order details, addresses).
* Arabic texts now also cover payment method titles and descriptions and shipping method names typed in WooCommerce > Settings (Cash on delivery, Pay with cash upon delivery., Flat rate, Free shipping...), including the payment method saved on each order. Add your own in Store tools > Arabic texts.

= 2.9.5 =
* Fix: the language switcher on translated WooCommerce pages (cart, checkout, my account, wishlist) goes to the page in the other language (for example /checkout/ -> /إتمام-الشراء/), keeping the account section, the order-received step with its order key, and the wishlist view. Before, it only removed /en/ from the address and landed on the English page.
* Quick view window: the wishlist and compare buttons are hidden, and the product title uses Alexandria on Arabic pages.
* Mini cart: product names use Alexandria in every language.

= 2.9.4 =
* Arabic texts: option names typed on products (like "Bra Sizes") and the color / size labels are translated on the product page, cart, mini cart, checkout and emails.
* Arabic texts: XStore's Compare (account menu, buttons) and Subtotal added.

= 2.9.3 =
* Filter bar: "Clear all" and choosing another category on a category page stay in the page's language on their own, without relying on the Polylang shop module: the shop link is built in the current language, and the click keeps the current page's language prefix (also for pages served from a cache).

= 2.9.2 =
* Category Slider: new "Arrow click moves" setting. "To the end / back to the start" (the default) jumps straight to the last or first cards; "One screen of cards" and "One card" are the other choices. The arrows now grey out correctly after every click.

= 2.9.1 =
* Fix: on Arabic pages, "Clear all" (and picking another category) in the filter bar went to the English shop. The shop page is an English page in Polylang, so its address was /en/shop/ everywhere; shop, category and cart/checkout/account links now follow the page language in every language, the default one included (filter bar, bottom bar, Return to shop, breadcrumbs, quick view).

= 2.9.0 =
* New: Arabic texts. XStore has no Arabic translation, so its storefront texts (reviews and the review form, Buy now, wishlist, quick view, mini cart, login, share buttons, photo lightbox, shop toolbar) and the size chart table are shown in Arabic on Arabic pages, including quick view and mini cart loaded in the background. WordPress and WooCommerce translations always come first. Add or change any text in Store tools > Arabic texts (one "English = Arabic" per line).

= 2.8.1 =
* Color swatches (product page, product cards, filter bar) take their color from the original color name, so they stay correct when the store Product Translations shows the name in Arabic ("إيكرو" still draws ECRU).

= 2.8.0 =
* New: cart, checkout, my account and terms page per language with free Polylang. WooCommerce > Language pages creates the English copies (design included, linked as Polylang translations), and WooCommerce then uses the page in the visitor's language for every link, redirect, account section, the order-received page and checkout requests (classic and block checkout). Setting: Store tools > Cart, checkout and account per language.

= 2.7.1 =
* Sale badge in Arabic reads "خصم 11%" (without حتى).

= 2.7.0 =
* New: product badges in each Polylang language. On Arabic pages "Up to 11%" becomes "خصم حتى 11%" and "New" becomes "جديد" (also Sale, Hot, Out of stock). Works on full pages and on products loaded by AJAX. Texts: WooCommerce > Settings > Products > Store tools > Product badges.

= 2.6.0 =
* New: XStore's mobile bottom bar (Home, Shop, Wishlist, Account...) in each Polylang language. Arabic pages show Arabic labels and links without /en/; English pages keep English. The theme's bar, icons and wishlist count stay as they are. Labels: WooCommerce > Settings > Products > Store tools > Mobile bottom bar.

= 2.5.1 =
* Fix: the Category Slider's title, subtitle and arrows were bunched in the middle. The slider shared a class name with the card color swatches, whose centering style leaked in; the slider now uses its own class names (.kmst-csl-*), so the title sits on one side and the arrows on the other.
* Elementor's cached page CSS is rebuilt once after updating, so saved slider styles apply right away.

= 2.5.0 =
* Category Slider: heading with a title and subtitle, with the arrows on the opposite side of the row.
* Category Slider: cards per view can be a half (4.5 by default), so part of the next card peeks in.

= 2.4.0 =
* New: "Category Slider" Elementor widget. Hand-made cards (image + title + link) in a sliding row, with hover zoom. Nothing is read from WooCommerce, so cards can point anywhere.

= 2.3.0 =
* Polylang shop languages now also covers cart, checkout, my account (orders, addresses, account details, ...), the wishlist and order tracking pages: /ar/my-account/, /ar/cart/, /ar/checkout/ no longer redirect to English, and links to them stay in Arabic.

= 2.2.0 =
* New: Arabic translation of all front-end texts (card buttons, size picker, filter bar, sort options).
* New: Horizontal Filters widget > Texts section to change Apply, Clear, Clear all, Show results, Min, Max, 5 stars, & up, the link copied message and each sort option. Empty fields use the translated default.

= 2.1.0 =
* New: Polylang shop languages. /ar/shop/, Arabic product categories, tags and products work with free Polylang instead of redirecting to English.

= 2.0.2 =
* Fix: the hover photo covered the wishlist heart and the theme's Quick View bar on hover.
* Wishlist heart back to showing on hover only (the always-visible option is now off by default).

= 2.0.1 =
* Wishlist heart on product cards is visible without hovering, on every page including the homepage.

= 2.0.0 =
* Card buttons and card color swatches are off on the homepage (new setting to turn them back on).
* New: product cards slide in the next photo on hover, site-wide.
* New: wishlist heart on a white circle (70% opacity) on all product cards.
* New: Sale, New, Hot and other product labels get 10px rounded corners (setting: Rounded product labels).
* Fix: missing space in "Up to 15%" labels.

= 1.9.2 =
* Fix: no space between the icon and the button label.

= 1.9.1 =
* Fix: "Add to cart Add to cart" shown twice on carousel and related product cards; those buttons now show the bag icon like the shop grid.

= 1.9.0 =
* Card buttons (and card color swatches) now appear on all XStore product cards: homepage carousels, related products and any product grid, not only the shop page.

= 1.8.0 =
* New: Rating filter in the filter bar (widget filter type "Rating", and a checkbox for the shortcode).

= 1.7.0 =
* Filter bar dropdowns open on hover on desktop (can be turned off in the widget).

= 1.6.2 =
* Card buttons: no border, 10px rounded corners.

= 1.6.1 =
* Card buttons have 15px rounded corners.

= 1.6.0 =
* New: color swatches on product cards that switch the card photo instantly, keeping the image size.

= 1.5.3 =
* Card button border and hover background use the Elementor primary color.
* Fix: the Add to cart bag icon stayed black on hover.

= 1.5.2 =
* Card button restyled: centered and sized to its text, white with a thin black border and black text; black with white text on hover.

= 1.5.1 =
* New setting: Card button visibility (On hover / Always visible).
* Fix: the Quick view eye icon could fade out with some XStore hover styles.

= 1.5.0 =
* Swatches now also show in the XStore quick view popup.
* Quick view / Add to cart is now a primary-color bar inside the product image.

= 1.4.0 =
* New: product card buttons. Quick view for products with colors, Add to cart with an in-place size picker for size-only products.

= 1.3.0 =
* New phone layout for the filter bar: Filter / Sort toolbar and bottom sheets, sized for touch.
* Fix: filters overlapped Sort by and View on phones.
* Fix: sheets now open above XStore's mobile bottom bar.
* Widget: Phone toolbar style section and Filter button label.

= 1.2.1 =
* Fix: filter bar buttons no longer pick up the site-wide button color from Elementor Site Settings.
* Widget style options for filter labels (text, background, border for normal, hover and open), Clear/Apply buttons, and chip background and radius.

= 1.2.0 =
* New: Horizontal Filters Elementor widget with drag-to-reorder filters, custom labels and style controls.

= 1.1.0 =
* New: horizontal shop filter bar, [kmst_filter_bar].
* Color names: Cappuccino / Capuccino, Visone / Mink, Latte, Powder, Cipria, Dusty Pink, Old Rose.

= 1.0.0 =
* Variation swatches, Hide Uncategorized and filter arrows combined in one plugin. Colors saved with KM Variation Swatches carry over.
