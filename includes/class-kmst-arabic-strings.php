<?php
/**
 * Arabic for the texts XStore (and typed-in content) still shows in English on Arabic pages.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * XStore has no Arabic translation, so its storefront texts (reviews, wishlist, mini cart, login, quick
 * view...) stay English on Arabic pages; some texts are typed into content (a size chart table, a template
 * subtitle). Two layers, both only on Arabic pages and never in the admin:
 *
 * - gettext: XStore, XStore Core and WooCommerce strings that have no translation yet get the Arabic below.
 *   Also reaches content loaded in the background (quick view, mini cart), whose language is taken from the
 *   page that loaded it.
 * - page text: on full page loads, any text (or aria-label / title / placeholder) that is exactly one of the
 *   entries is replaced, which covers typed-in texts.
 *
 * Extra or different wording: Store tools > Arabic texts, one "English = Arabic" per line.
 */
final class KMST_Arabic_Strings {

	/**
	 * The built-in dictionary, English => Arabic.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			// Header, mini cart, account and wishlist panels.
			'Add all to cart'                  => 'أضف الكل إلى السلة',
			'Checkout'                         => 'إتمام الشراء',
			'Proceed to checkout'              => 'إتمام الشراء',
			'Login'                            => 'تسجيل الدخول',
			'Log in'                           => 'تسجيل الدخول',
			'Sign in'                          => 'تسجيل الدخول',
			'Sign In'                          => 'تسجيل الدخول',
			'Lost your password?'              => 'نسيت كلمة المرور؟',
			'Password'                         => 'كلمة المرور',
			'Remember me'                      => 'تذكريني',
			'Required'                         => 'مطلوب',
			'Username or email'                => 'اسم المستخدم أو البريد الإلكتروني',
			'Username or email address'        => 'اسم المستخدم أو البريد الإلكتروني',
			'Register'                         => 'إنشاء حساب',
			'Create an account'                => 'إنشاء حساب',
			'My Account'                       => 'حسابي',
			'My account'                       => 'حسابي',
			'Logout'                           => 'تسجيل الخروج',
			'Menu'                             => 'القائمة',
			'Mobile Menu'                      => 'القائمة',
			'Search'                           => 'بحث',
			'Search for'                       => 'ابحثي عن',
			'Search for products'              => 'ابحثي عن المنتجات',
			'Search products'                  => 'البحث عن المنتجات',
			'Search products…'                 => 'البحث عن المنتجات...',
			'Search products...'               => 'البحث عن المنتجات...',
			'Select search category'           => 'اختاري فئة البحث',
			'All categories'                   => 'كل الفئات',
			'What Are You Looking For?'        => 'عمّ تبحثين؟',
			'No results were found!'           => 'لم يتم العثور على نتائج!',
			'View all results'                 => 'عرض كل النتائج',
			'No results were found.'           => 'لم يتم العثور على نتائج.',
			'{{count}} item found'             => 'تم العثور على {{count}} منتج',
			'{{count}} items found'            => 'تم العثور على {{count}} منتجات',
			'Unfortunately, there are no products that match your criteria' => 'عذرًا، لا توجد منتجات تطابق بحثك',
			'Products'                         => 'المنتجات',
			'Pages'                            => 'الصفحات',
			'Posts'                            => 'المقالات',
			'Category: %s'                     => 'الفئة: %s',
			'Login to view price'              => 'سجّلي الدخول لعرض السعر',
			'No products in the cart.'         => 'لا توجد منتجات في السلة.',
			'No products in the wishlist.'     => 'لا توجد منتجات في المفضلة.',
			'Shopping Cart'                    => 'سلة التسوق',
			'Shopping cart'                    => 'سلة التسوق',
			'Cart'                             => 'السلة',
			'View cart'                        => 'عرض السلة',
			'Subtotal:'                        => 'المجموع الفرعي:',
			'Return To Shop'                   => 'العودة إلى المتجر',
			'Return to shop'                   => 'العودة إلى المتجر',
			'Continue shopping'                => 'متابعة التسوق',
			'YOUR SHOPPING CART IS EMPTY'      => 'سلة التسوق فارغة',
			'We invite you to get acquainted with an assortment of our shop. Surely you can find something for yourself!' => 'ندعوكِ للتعرّف على تشكيلة متجرنا، ستجدين بالتأكيد ما يناسبكِ!',
			'Your compare is empty'            => 'قائمة المقارنة فارغة',
			'Your wishlist is empty'           => 'المفضلة فارغة',
			'Your shopping cart is empty'      => 'سلة التسوق فارغة',
			'Wishlist'                         => 'المفضلة',
			'View Wishlist'                    => 'عرض المفضلة',
			'Add to Wishlist'                  => 'أضيفي إلى المفضلة',
			'Remove from Wishlist'             => 'إزالة من المفضلة',
			// The notice that pops up after adding something (the link and the product name are the %s).
			'<a href="%s">%s</a> has been added to the wishlist' => 'تمت إضافة <a href="%s">%s</a> إلى المفضلة',
			'<a href="%s">%s</a> has been added to the compare'  => 'تمت إضافة <a href="%s">%s</a> إلى المقارنة',
			'<a href="%s">%s</a> has been added to the waitlist' => 'تمت إضافة <a href="%s">%s</a> إلى قائمة الانتظار',
			'<a href="%s">%s</a> has been added to your cart'    => 'تمت إضافة <a href="%s">%s</a> إلى سلتك',
			'%s has been added to the wishlist' => 'تمت إضافة %s إلى المفضلة',
			'%s has been added to your cart'    => 'تمت إضافة %s إلى سلتك',
			'has been added to the wishlist'   => 'تمت إضافته إلى المفضلة',
			'has been added to your cart'      => 'تمت إضافته إلى سلتك',
			// Wishlist page (XStore Wishlist).
			'Action'                           => 'الإجراء',
			'Stock status'                     => 'حالة التوفر',
			'Ask for an estimate'              => 'اطلبي عرض سعر',
			'Share on:'                        => 'شاركي على:',
			'Added on: %s'                     => 'أضيف في: %s',
			'Are you sure?'                    => 'هل أنتِ متأكدة؟',
			'Bulk select'                      => 'تحديد متعدد',
			'Copy URL'                         => 'نسخ الرابط',
			'Delete'                           => 'حذف',
			'Browse wishlist'                  => 'تصفح المفضلة',
			'View wishlist'                    => 'عرض المفضلة',
			'Add to wishlist'                  => 'أضيفي إلى المفضلة',
			'Add to my wishlist'               => 'أضيفي إلى المفضلة',
			'Remove from my wishlist'          => 'إزالة من المفضلة',
			'Remove %s from wishlist'          => 'إزالة %s من المفضلة',
			'Please, choose any product by clicking checkbox' => 'يرجى اختيار منتج بالضغط على المربع',
			'Sorry, there are no products available for this action' => 'عذرًا، لا توجد منتجات متاحة لهذا الإجراء',
			'Sorry, %s is no longer available and was removed from your wishlist.' => 'عذرًا، %s لم يعد متوفرًا وتمت إزالته من المفضلة.',
			'It seems all products from your wishlist are missing on this web-site' => 'يبدو أن جميع منتجات المفضلة لم تعد متوفرة في المتجر',
			'See my wishlist on %s'            => 'شاهدي مفضلتي على %s',
			'Shared wishlist by %s'            => 'مفضلة مشاركة من %s',
			'Buy one of this item'             => 'اشتري واحدة من هذا المنتج',
			'N/A'                              => 'غير متاح',
			'Compare'                          => 'المقارنة',
			'View Compare'                     => 'عرض المقارنة',
			'Add to Compare'                   => 'أضيفي إلى المقارنة',
			'Remove from my compare'           => 'إزالة من المقارنة',
			'Subtotal'                         => 'المجموع الفرعي',
			'SUBTOTAL:'                        => 'المجموع الفرعي:',

			// Cart and checkout (XStore's cart / checkout widgets).
			'Your order'                       => 'طلبك',
			'Total'                            => 'الإجمالي',
			'Remove'                           => 'إزالة',
			'Change address'                   => 'تغيير العنوان',
			'Calculate shipping'               => 'حساب الشحن',
			'Shipping'                         => 'الشحن',
			'Shipping methods'                 => 'طرق الشحن',
			'Shipping method'                  => 'طريقة الشحن',
			'Sorry, it seems that there are no shipping options available. Please contact us if you require assistance or wish to make alternate arrangements.' => 'عذرًا، لا توجد طرق شحن متاحة لعنوانك. تواصلي معنا لمساعدتك أو لترتيب طريقة أخرى.',
			'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.' => 'لا توجد طرق شحن متاحة. تأكدي من إدخال عنوانك بشكل صحيح، أو تواصلي معنا للمساعدة.',
			'No shipping options were found for %s.' => 'لم يتم العثور على طرق شحن لـ %s.',
			'Enter a different address'        => 'أدخلي عنوانًا آخر',
			'Shipping options will be updated during checkout.' => 'سيتم تحديث خيارات الشحن أثناء إتمام الشراء.',
			'Shipping costs are calculated during checkout.' => 'يتم حساب تكلفة الشحن أثناء إتمام الشراء.',
			'Estimated delivery'               => 'موعد التوصيل المتوقع',

			// The checkout privacy and terms lines (WooCommerce keeps these as settings, with the link as
			// a shortcode, so the shortcode has to survive the translation).
			'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our [privacy_policy].' => 'سيتم استخدام بياناتك الشخصية لمعالجة طلبك، ولدعم تجربتك في هذا الموقع، ولأغراض أخرى موضّحة في [privacy_policy].',
			'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our' => 'سيتم استخدام بياناتك الشخصية لمعالجة طلبك، ولدعم تجربتك في هذا الموقع، ولأغراض أخرى موضّحة في',
			'I have read and agree to the website [terms].' => 'لقد قرأتُ [terms] وأوافق عليها.',
			'I have read and agree to the website terms and conditions' => 'لقد قرأتُ الشروط والأحكام وأوافق عليها',
			'privacy policy'                   => 'سياسة الخصوصية',
			'terms and conditions'             => 'الشروط والأحكام',

			// Payment methods (titles and descriptions typed in WooCommerce > Payments).
			'Credit Card'                      => 'بطاقة ائتمانية',
			'Credit card'                      => 'بطاقة ائتمانية',
			'Debit Card'                       => 'بطاقة مدى',
			'Cash on delivery'                 => 'الدفع عند الاستلام',
			'Cash on Delivery'                 => 'الدفع عند الاستلام',
			'Pay with cash upon delivery.'     => 'ادفعي نقدًا عند استلام طلبك.',
			'Pay with your credit card via Tap payment gateway. On clicking Place order payment will be processed.' => 'ادفعي ببطاقتك عبر بوابة Tap. يتم تنفيذ الدفع بعد الضغط على تأكيد الطلب.',
			'Pay with your credit card via Tap payment gateway. On clicking Place order payment will be processed. TEST MODE ENABLED. In test mode, you can use the card numbers mentioned in documentation' => 'ادفعي ببطاقتك عبر بوابة Tap. يتم تنفيذ الدفع بعد الضغط على تأكيد الطلب. (وضع التجربة مفعّل: استخدمي أرقام البطاقات التجريبية.)',
			'TEST MODE ENABLED. In test mode, you can use the card numbers mentioned in documentation' => 'وضع التجربة مفعّل: استخدمي أرقام البطاقات التجريبية الموضحة في التوثيق.',
			'Shipping to %s.'                  => 'الشحن إلى %s.',
			'Update cart'                      => 'تحديث السلة',
			'Apply coupon'                     => 'تطبيق الكوبون',
			'Clear shopping cart'              => 'إفراغ السلة',
			'Coupon code'                      => 'رمز الكوبون',
			'Have a coupon?'                   => 'لديكِ كوبون؟',
			'Place order'                      => 'تأكيد الطلب',
			'Product'                          => 'المنتج',
			'Quantity'                         => 'الكمية',

			// My account dashboard ([woocommerce_my_account] with XStore's dashboard).
			'Welcome to your account page'     => 'مرحبًا بكِ في صفحة حسابك',
			'Hi %1$s, today is a great day to check %2$s page. You can check also:' => 'أهلًا %1$s، اليوم يوم رائع لتصفّح %2$s. يمكنكِ أيضًا الاطلاع على:',
			'your account'                     => 'حسابك',
			'Recent orders'                    => 'أحدث الطلبات',
			'Addresses'                        => 'العناوين',
			'Account details'                  => 'تفاصيل الحساب',
			'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)' => 'أهلًا %1$s (لستِ %1$s؟ <a href="%2$s">تسجيل الخروج</a>)',
			'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a>, and <a href="%3$s">edit your password and account details</a>.' => 'من لوحة حسابك يمكنكِ عرض <a href="%1$s">أحدث الطلبات</a>، وإدارة <a href="%2$s">عناوين الشحن والفواتير</a>، و<a href="%3$s">تعديل كلمة المرور وتفاصيل الحساب</a>.',
			'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">billing address</a>, and <a href="%3$s">edit your password and account details</a>.' => 'من لوحة حسابك يمكنكِ عرض <a href="%1$s">أحدث الطلبات</a>، وإدارة <a href="%2$s">عنوان الفاتورة</a>، و<a href="%3$s">تعديل كلمة المرور وتفاصيل الحساب</a>.',
			'You may also like...'             => 'قد يعجبكِ أيضًا…',
			'Dashboard'                        => 'لوحة التحكم',
			'Orders'                           => 'الطلبات',
			'Downloads'                        => 'التنزيلات',
			'Payment methods'                  => 'طرق الدفع',
			'Log out'                          => 'تسجيل الخروج',

			// Checkout steps and the order-received page.
			'Order status'                     => 'حالة الطلب',
			'Order Status'                     => 'حالة الطلب',
			'Order number:'                    => 'رقم الطلب:',
			'Order:'                           => 'الطلب:',
			'Date:'                            => 'التاريخ:',
			'Email:'                           => 'البريد الإلكتروني:',
			'Total:'                           => 'الإجمالي:',
			'Payment method:'                  => 'طريقة الدفع:',
			'Shipping:'                        => 'الشحن:',
			'Order details'                    => 'تفاصيل الطلب',
			'Billing address'                  => 'عنوان الفاتورة',
			'Shipping address'                 => 'عنوان الشحن',
			'Thank you. Your order has been received.' => 'شكرًا لكِ. تم استلام طلبك.',
			'Order again'                      => 'اطلبي مرة أخرى',

			// Payment and shipping method names typed in WooCommerce > Settings (one value for all languages).
			'Cash on delivery'                 => 'الدفع عند الاستلام',
			'Pay with cash upon delivery.'     => 'الدفع نقدًا عند استلام الطلب.',
			'Direct bank transfer'             => 'تحويل بنكي مباشر',
			'Check payments'                   => 'الدفع بشيك',
			'Flat rate'                        => 'رسوم شحن ثابتة',
			'Free shipping'                    => 'شحن مجاني',
			'Local pickup'                     => 'استلام من المتجر',

			// Option names typed on products (custom attributes), shown on the product page and in the cart.
			'Bra Sizes'                        => 'مقاس حمالة الصدر',
			'Bra Size'                         => 'مقاس حمالة الصدر',
			'color'                            => 'اللون',
			'Color'                            => 'اللون',
			'size'                             => 'المقاس',
			'Size'                             => 'المقاس',
			'Home'                             => 'الرئيسية',
			'Shop'                             => 'المتجر',
			'Recommended for you'              => 'مقترحة لكِ',
			'Refresh'                          => 'تحديث',
			'Close'                            => 'إغلاق',
			'Next'                             => 'التالي',
			'next'                             => 'التالي',
			'Previous'                         => 'السابق',
			'Privacy Policy'                   => 'سياسة الخصوصية',

			// Shop page.
			'Quick View'                       => 'نظرة سريعة',
			'Quick view'                       => 'نظرة سريعة',
			'Filters'                          => 'الفلاتر',
			'Categories'                       => 'الفئات',
			'All'                              => 'الكل',
			'Apply'                            => 'تطبيق',
			'Clear'                            => 'مسح',
			'Clear all'                        => 'مسح الكل',
			'Show results'                     => 'عرض النتائج',
			'Price'                            => 'السعر',
			'Min'                              => 'من',
			'Max'                              => 'إلى',
			'Show'                             => 'عرض',
			'Products per page'                => 'منتجات في الصفحة',
			'List'                             => 'قائمة',
			'2 columns grid'                   => 'عمودان',
			'3 columns grid'                   => '3 أعمدة',
			'4 columns grid'                   => '4 أعمدة',
			'5 columns grid'                   => '5 أعمدة',
			'6 columns grid'                   => '6 أعمدة',
			'1 column'                         => 'عمود واحد',
			'2 columns'                        => 'عمودان',
			'3 columns'                        => '3 أعمدة',
			'4 columns'                        => '4 أعمدة',
			'Filter by Color'                  => 'تصفية حسب اللون',
			'Filter by Size'                   => 'تصفية حسب المقاس',
			'Add to cart'                      => 'أضيفي إلى السلة',
			'Select options'                   => 'اختاري الخيارات',
			'Read more'                        => 'اقرئي المزيد',
			'Out of stock'                     => 'نفدت الكمية',
			'In stock'                         => 'متوفر',
			'New'                              => 'جديد',

			// Single product.
			'Buy now'                          => 'اشتري الآن',
			'or'                               => 'أو',
			'Product Details'                  => 'تفاصيل المنتج',
			'Description'                      => 'الوصف',
			'Additional information'           => 'معلومات إضافية',
			'Categories:'                      => 'الفئات:',
			'Category:'                        => 'الفئة:',
			'Tags:'                            => 'الوسوم:',
			'SKU:'                             => 'رمز المنتج:',
			'Share:'                           => 'مشاركة:',
			'Related Products'                 => 'منتجات ذات صلة',
			'Related products'                 => 'منتجات ذات صلة',
			'You may also like&hellip;'        => 'قد يعجبكِ أيضًا…',
			'Reviews'                          => 'المراجعات',
			'There are no reviews yet.'        => 'لا توجد مراجعات بعد.',
			'Be the first to review &ldquo;%s&rdquo;' => 'كوني أول من يقيّم &ldquo;%s&rdquo;',
			'Your email address will not be published. Required fields are marked' => 'لن يتم نشر بريدك الإلكتروني. الحقول المطلوبة مشار إليها بـ',
			'Your rating'                      => 'تقييمك',
			'Rate&hellip;'                     => 'قيّمي…',
			'Rate…'                            => 'قيّمي…',
			'Perfect'                          => 'ممتاز',
			'Good'                             => 'جيد',
			'Average'                          => 'متوسط',
			'Not that bad'                     => 'ليس سيئًا',
			'Very poor'                        => 'سيئ جدًا',
			'Your review'                      => 'مراجعتك',
			'Name'                             => 'الاسم',
			'Email'                            => 'البريد الإلكتروني',
			'Submit'                           => 'إرسال',

			// Photo lightbox and share buttons.
			'Close (Esc)'                      => 'إغلاق (Esc)',
			'Previous (arrow left)'            => 'السابق',
			'Next (arrow right)'               => 'التالي',
			'Share on facebook'                => 'مشاركة على فيسبوك',
			'Share on twitter'                 => 'مشاركة على إكس',
			'Share on linkedin'                => 'مشاركة على لينكدإن',
			'Share on pinterest'               => 'مشاركة على بنترست',
			'Share on email'                   => 'مشاركة عبر البريد الإلكتروني',
			'Share on whatsapp'                => 'مشاركة على واتساب',
			'Breadcrumb'                       => 'مسار التنقل',

			// Texts typed into the product template.
			'You might also like these — similar styles to help you find your perfect fit.' => 'قد يعجبكِ أيضًا — تصاميم مشابهة تساعدكِ على إيجاد المقاس المثالي.',

			// Size chart table.
			'Size chart'                       => 'جدول المقاسات',
			'Bra'                              => 'حمالة الصدر',
			'Panties - Boxers'                 => 'السراويل الداخلية - البوكسر',
			'Homewear - Pajamas'               => 'ملابس المنزل - البيجامات',
			'Plaj'                             => 'ملابس البحر',
			'Pantyhose'                        => 'الجوارب النسائية',
			'TR - Body'                        => 'المقاس التركي',
			'INT - Body'                       => 'المقاس الدولي',
			'Body'                             => 'المقاس',
			'Measurement (cm)'                 => 'القياس (سم)',
			'Chest Circumference - Cup Size (cm)' => 'محيط الصدر - مقاس الكوب (سم)',
			'A Cup'                            => 'كوب A',
			'B Cup'                            => 'كوب B',
			'C Cup'                            => 'كوب C',
			'D Cup'                            => 'كوب D',
			'E Cup'                            => 'كوب E',
			'F Cup'                            => 'كوب F',
			'Waist (cm)'                       => 'الخصر (سم)',
			'Bel (cm)'                         => 'الخصر (سم)',
			'Hips (cm)'                        => 'الأرداف (سم)',
			'Chest (cm)'                       => 'الصدر (سم)',
			'Under Chest (cm)'                 => 'تحت الصدر (سم)',
			'Inner Leg Length(cm)'             => 'طول الساق الداخلي (سم)',
			'Length (cm)'                      => 'الطول (سم)',
			'Height (cm)'                      => 'الطول (سم)',
			'Kilo (kg)'                        => 'الوزن (كجم)',
			'Yaş'                              => 'العمر',
			'Women Homewear - Pajamas'         => 'ملابس المنزل النسائية - البيجامات',
			'Girl Child Homewear - Pajamas'    => 'ملابس المنزل للبنات - البيجامات',
			"Men's Homewear / Pajamas"         => 'ملابس المنزل الرجالية - البيجامات',
			'Boys Homewear / Pajamas'          => 'ملابس المنزل للأولاد - البيجامات',
			"Women's Swimsuit"                 => 'مايوه نسائي',
			"Women's Bikini Top"               => 'بكيني - الجزء العلوي',
			"Women's Bikini Bottom"            => 'بكيني - الجزء السفلي',
		);
	}

	/**
	 * Dictionary for this request (defaults + the site's own lines), or null before it is built.
	 *
	 * @var array|null
	 */
	private $map = null;

	/**
	 * Same dictionary with keys as they appear on the page (entities decoded, spaces squeezed).
	 *
	 * @var array|null
	 */
	private $page_map = null;

	/**
	 * Arabic page? Cached once the language is known.
	 *
	 * @var bool|null
	 */
	private $active = null;

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_filter( 'gettext', array( $this, 'gettext' ), 30, 3 );
		add_filter( 'gettext_with_context', array( $this, 'gettext_with_context' ), 30, 4 );
		add_filter( 'ngettext', array( $this, 'ngettext' ), 30, 5 );
		add_filter( 'woocommerce_attribute_label', array( $this, 'attribute_label' ), 30, 2 );
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'filter_fragments' ), 99 );
		// Background requests follow the page that sent them, not the visitor's language cookie. Three
		// hooks, because each layer decides on its own: Polylang picks the language, WordPress picks the
		// locale for the translation files, and determine_locale answers anything asked later.
		add_filter( 'pll_preferred_language', array( __CLASS__, 'background_language' ), 20 );
		add_filter( 'locale', array( __CLASS__, 'background_locale' ), 99 );
		add_filter( 'determine_locale', array( __CLASS__, 'background_locale' ), 99 );
		// Titles typed in WooCommerce settings, which are not translation strings.
		add_filter( 'woocommerce_gateway_title', array( $this, 'setting_text' ), 30 );
		add_filter( 'woocommerce_gateway_description', array( $this, 'setting_text' ), 30 );
		add_filter( 'woocommerce_shipping_rate_label', array( $this, 'setting_text' ), 30 );
		add_filter( 'woocommerce_get_privacy_policy_text', array( $this, 'setting_text' ), 30 );
		add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', array( $this, 'setting_text' ), 30 );
		// Saved on each order: order-received page, My account > Orders, order emails shown on screen.
		add_filter( 'woocommerce_order_get_payment_method_title', array( $this, 'setting_text' ), 30 );
		add_action( 'template_redirect', array( $this, 'start' ), 3 );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'settings' ), 22, 2 );
	}

	/* ------------------------------------------------------------------ */
	/* When                                                                */
	/* ------------------------------------------------------------------ */

	/**
	 * Is this an Arabic page (or a background request made from one)?
	 *
	 * @return bool
	 */
	private function active() {
		if ( null !== $this->active ) {
			return $this->active;
		}
		if ( ( is_admin() && ! wp_doing_ajax() ) || ! KMST_Settings::enabled( 'ar_strings' ) ) {
			$this->active = false;
			return false;
		}

		$lang = self::language();
		if ( '' === $lang ) {
			return false; // Not known yet; ask again later.
		}

		$languages    = (array) apply_filters( 'kmst_arabic_strings_languages', array( 'ar' ) );
		$this->active = in_array( $lang, $languages, true );

		return $this->active;
	}

	/**
	 * The visitor's language: Polylang's, or for background requests (?wc-ajax=, admin-ajax.php, /wp-json/,
	 * which have no language prefix) the language of the page that sent them. Without Polylang, the site locale.
	 *
	 * @return string
	 */
	/**
	 * WooCommerce redraws parts of the cart and checkout in the background (wc-ajax=update_order_review and
	 * the cart fragments). Those requests carry no language in the URL, so Polylang falls back to the
	 * visitor's language cookie: someone who browsed Arabic first, then opened the English checkout, gets
	 * the Arabic WooCommerce translation of whatever is redrawn (the shipping row, the terms sentence, the
	 * Place order button) inside an English page.
	 *
	 * The page that sent the request is the one that decides, so the locale follows it.
	 *
	 * @param string $locale Locale WordPress worked out.
	 * @return string
	 */
	public static function background_language( $language ) {
		if ( ! self::is_background() ) {
			return $language;
		}
		$slug = self::language();
		return '' !== $slug ? $slug : $language;
	}

	/**
	 * Is this a request made by a page in the background (cart fragments, update_order_review, Store API)?
	 *
	 * @return bool
	 */
	private static function is_background() {
		if ( is_admin() || empty( $_SERVER['HTTP_REFERER'] ) ) {
			return false;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return wp_doing_ajax() || isset( $_GET['wc-ajax'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			|| ( function_exists( 'rest_get_url_prefix' ) && false !== strpos( $uri, '/' . rest_get_url_prefix() . '/' ) );
	}

	/**
	 * The locale of a background request: the one its page uses.
	 *
	 * @param string $locale Locale WordPress worked out.
	 * @return string
	 */
	public static function background_locale( $locale ) {
		if ( ! function_exists( 'PLL' ) || ! self::is_background() ) {
			return $locale;
		}

		$slug = self::language();
		if ( '' === $slug ) {
			return $locale;
		}

		$pll = PLL();
		if ( empty( $pll->model ) || ! method_exists( $pll->model, 'get_language' ) ) {
			return $locale;
		}

		$language = $pll->model->get_language( $slug );
		return ( $language && ! empty( $language->locale ) ) ? (string) $language->locale : $locale;
	}

	public static function language() {
		if ( ! function_exists( 'pll_current_language' ) ) {
			return substr( (string) determine_locale(), 0, 2 );
		}

		$uri        = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$background = wp_doing_ajax() || isset( $_GET['wc-ajax'] ) || isset( $_GET['rest_route'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			|| ( function_exists( 'rest_get_url_prefix' ) && false !== strpos( $uri, '/' . rest_get_url_prefix() . '/' ) );

		if ( $background && ! empty( $_SERVER['HTTP_REFERER'] ) && function_exists( 'PLL' ) ) {
			$referer = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
			$site    = wp_parse_url( home_url(), PHP_URL_HOST );
			$from    = wp_parse_url( $referer, PHP_URL_HOST );
			$pll     = PLL();
			if ( $site && $from && strtolower( $site ) === strtolower( $from ) && false === strpos( $referer, '/wp-admin/' )
				&& ! empty( $pll->links_model ) && method_exists( $pll->links_model, 'get_language_from_url' ) ) {
				$lang = (string) $pll->links_model->get_language_from_url( $referer );
				if ( '' === $lang && function_exists( 'pll_default_language' ) ) {
					$lang = (string) pll_default_language( 'slug' );
				}
				if ( '' !== $lang ) {
					return $lang;
				}
			}
		}

		return (string) pll_current_language( 'slug' );
	}

	/* ------------------------------------------------------------------ */
	/* Dictionary                                                          */
	/* ------------------------------------------------------------------ */

	/**
	 * Defaults plus the site's own "English = Arabic" lines (which win).
	 *
	 * @return array
	 */
	private function map() {
		if ( null !== $this->map ) {
			return $this->map;
		}
		$map = self::defaults();
		foreach ( self::parse_lines( (string) get_option( 'kmst_ar_strings_extra', '' ) ) as $en => $ar ) {
			$map[ $en ] = $ar;
		}
		$this->map = (array) apply_filters( 'kmst_arabic_strings', $map );
		return $this->map;
	}

	/**
	 * "English = Arabic" lines into pairs. An empty Arabic side removes a built-in entry.
	 *
	 * @param string $text Lines.
	 * @return array
	 */
	public static function parse_lines( $text ) {
		$pairs = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
			if ( false === strpos( $line, '=' ) ) {
				continue;
			}
			list( $en, $ar ) = array_map( 'trim', explode( '=', $line, 2 ) );
			if ( '' !== $en ) {
				$pairs[ $en ] = $ar;
			}
		}
		return $pairs;
	}

	/**
	 * Arabic for an exact English string, or '' when there is none.
	 *
	 * @param string $text English.
	 * @return string
	 */
	private function lookup( $text ) {
		$map = $this->map();
		return ( isset( $map[ $text ] ) && '' !== $map[ $text ] ) ? $map[ $text ] : '';
	}

	/* ------------------------------------------------------------------ */
	/* Layer 1: gettext                                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Domains whose untranslated strings are filled in.
	 *
	 * @param string $domain Text domain.
	 * @return bool
	 */
	private function wanted_domain( $domain ) {
		return in_array( $domain, (array) apply_filters( 'kmst_arabic_strings_domains', array( 'xstore', 'xstore-core', 'woocommerce', 'default' ) ), true );
	}

	/**
	 * Singular strings. A translation from a language pack always wins; only English leftovers are filled.
	 *
	 * @param string $translation Translation.
	 * @param string $text        Original.
	 * @param string $domain      Domain.
	 * @return string
	 */
	public function gettext( $translation, $text, $domain ) {
		if ( $translation !== $text || ! $this->wanted_domain( $domain ) || ! $this->active() ) {
			return $translation;
		}
		$own = self::domain_strings();
		if ( isset( $own[ $domain ][ $text ] ) ) {
			return $own[ $domain ][ $text ];
		}
		$ar = $this->lookup( $text );
		return '' !== $ar ? $ar : $translation;
	}

	/**
	 * Words that are only safe to translate inside one text domain, because the same English word means
	 * something else elsewhere. "OK" is the coupon button in the theme's cart, but "موافق" in a dialog.
	 * These are not used by the page filter.
	 *
	 * @return array
	 */
	private static function domain_strings() {
		return array(
			'xstore' => array(
				'OK' => 'تطبيق',
			),
		);
	}

	/**
	 * Strings with context.
	 *
	 * @param string $translation Translation.
	 * @param string $text        Original.
	 * @param string $context     Context.
	 * @param string $domain      Domain.
	 * @return string
	 */
	public function gettext_with_context( $translation, $text, $context, $domain ) {
		return $this->gettext( $translation, $text, $domain );
	}

	/**
	 * Plural strings: the dictionary holds the English form used for the count.
	 *
	 * @param string $translation Translation.
	 * @param string $single      Singular.
	 * @param string $plural      Plural.
	 * @param int    $number      Count.
	 * @param string $domain      Domain.
	 * @return string
	 */
	public function ngettext( $translation, $single, $plural, $number, $domain ) {
		$english = 1 === (int) $number ? $single : $plural;
		return $this->gettext( $translation, $english, $domain );
	}

	/**
	 * Option names on the product page, in the cart, mini cart, checkout and emails. Covers options typed on a
	 * product (like "Bra Sizes"), which have no attribute to translate; attributes already translated elsewhere
	 * (the store Product Translations) arrive here in Arabic and are left as they are.
	 *
	 * @param string $label Label.
	 * @param string $name  Attribute name.
	 * @return string
	 */
	public function attribute_label( $label, $name = '' ) {
		if ( ! $this->active() || preg_match( '/\p{Arabic}/u', (string) $label ) ) {
			return $label;
		}
		$ar = $this->lookup( trim( (string) $label ) );
		return '' !== $ar ? $ar : $label;
	}

	/**
	 * Payment method titles and descriptions and shipping method names, typed in WooCommerce > Settings.
	 * Also runs for the checkout's order summary, which is refreshed in the background.
	 *
	 * @param string $text Text from the settings.
	 * @return string
	 */
	public function setting_text( $text ) {
		if ( ! is_string( $text ) || '' === $text || ! $this->active() || preg_match( '/\p{Arabic}/u', $text ) ) {
			return $text;
		}
		// Gateway descriptions arrive with line breaks and tags around them, so the spacing is evened out
		// before looking the text up.
		$plain = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) );
		$ar    = $this->lookup( $plain );
		return '' !== $ar ? $ar : $text;
	}

	/* ------------------------------------------------------------------ */
	/* Layer 2: exact texts on the page                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Buffer full page loads.
	 */
	public function start() {
		if ( is_feed() || wp_doing_ajax() || ! $this->active() ) {
			return;
		}
		ob_start( array( $this, 'filter_page' ) );
	}

	/**
	 * Key as it appears on the page.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function page_key( $text ) {
		$text = html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = str_replace( "\xC2\xA0", ' ', $text ); // Non-breaking spaces.
		return trim( preg_replace( '/\s+/u', ' ', $text ) );
	}

	/**
	 * Replace texts and a few attributes that are exactly a dictionary entry. Scripts, styles and form
	 * fields are left alone.
	 *
	 * @param string $html Page HTML.
	 * @return string
	 */
	public function filter_page( $html ) {
		if ( '' === $html || false === stripos( $html, '<body' ) ) {
			return $html;
		}

		$body = stripos( $html, '<body' );
		return substr( $html, 0, $body ) . $this->replace_texts( substr( $html, $body ) );
	}

	/**
	 * The mini cart and the other bits WooCommerce refreshes over AJAX are not part of a page, so the
	 * output buffer above never sees them. Same replacement, on each fragment.
	 *
	 * @param array $fragments Fragments, keyed by selector.
	 * @return array
	 */
	public function filter_fragments( $fragments ) {
		if ( ! is_array( $fragments ) || ! $this->active() ) {
			return $fragments;
		}
		foreach ( $fragments as $key => $html ) {
			if ( is_string( $html ) && '' !== $html ) {
				$fragments[ $key ] = $this->replace_texts( $html );
			}
		}
		return $fragments;
	}

	/**
	 * Replace the dictionary texts in a piece of HTML.
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	private function replace_texts( $html ) {
		if ( null === $this->page_map ) {
			$this->page_map = array();
			foreach ( $this->map() as $en => $ar ) {
				if ( '' !== $ar && false === strpos( $en, '%' ) ) {
					$this->page_map[ self::page_key( $en ) ] = $ar;
				}
			}
		}
		$map = $this->page_map;
		if ( ! $map ) {
			return $html;
		}

		$rest = preg_replace_callback(
			'#(<(script|style|textarea|code|pre)\b[^>]*>.*?</\2\s*>)|(\s(?:aria-label|title|placeholder|data-text)=")([^"]*)(")|>([^<>]+)(?=<)#is',
			static function ( $m ) use ( $map ) {
				if ( '' !== $m[1] ) {
					return $m[1]; // Untouched block.
				}
				if ( isset( $m[3] ) && '' !== $m[3] ) {
					$key = self::page_key( $m[4] );
					return isset( $map[ $key ] ) ? $m[3] . esc_attr( $map[ $key ] ) . $m[5] : $m[0];
				}
				$text = $m[6];
				$key  = self::page_key( $text );
				if ( '' === $key || ! isset( $map[ $key ] ) ) {
					return $m[0];
				}
				// Keep the spacing around the text.
				preg_match( '/^(\s*)/u', $text, $lead );
				preg_match( '/(\s*)$/u', $text, $trail );
				return '>' . $lead[1] . esc_html( $map[ $key ] ) . $trail[1];
			},
			$html
		);

		return null === $rest ? $html : $rest;
	}

	/* ------------------------------------------------------------------ */
	/* Settings                                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * Store tools > Arabic texts.
	 *
	 * @param array  $settings Settings.
	 * @param string $section  Section.
	 * @return array
	 */
	public function settings( $settings, $section ) {
		if ( 'kmst' !== $section ) {
			return $settings;
		}

		$settings[] = array(
			'title' => __( 'Arabic texts', 'km-storefront-builder' ),
			'type'  => 'title',
			'desc'  => sprintf(
				/* translators: %d: number of built-in texts */
				__( 'XStore has no Arabic translation, so %d of its storefront texts (reviews, wishlist, mini cart, login, quick view, share buttons...) and your size chart table are translated on Arabic pages by Store Tools. WordPress and WooCommerce translations always come first.', 'km-storefront-builder' ),
				count( self::defaults() )
			),
			'id'    => 'kmst_ar_strings_options',
		);
		$settings[] = array(
			'title'   => __( 'Translate the remaining texts', 'km-storefront-builder' ),
			'desc'    => __( 'Show these texts in Arabic on Arabic pages', 'km-storefront-builder' ),
			'id'      => 'kmst_ar_strings',
			'type'    => 'checkbox',
			'default' => 'yes',
		);
		$settings[] = array(
			'title'       => __( 'Your own texts', 'km-storefront-builder' ),
			'desc'        => __( 'One per line: English = Arabic. Copy the English exactly as it appears on the page. A line here replaces the built-in Arabic for the same text; "Text =" with nothing after it switches a built-in one off.', 'km-storefront-builder' ),
			'desc_tip'    => false,
			'id'          => 'kmst_ar_strings_extra',
			'type'        => 'textarea',
			'default'     => '',
			'placeholder' => "Free shipping over 500 SAR = شحن مجاني للطلبات فوق 500 ريال\nRelated Products = قد يعجبكِ أيضًا",
			'css'         => 'min-height:160px;width:100%;max-width:640px;font-family:monospace;',
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'kmst_ar_strings_options',
		);

		return $settings;
	}
}
