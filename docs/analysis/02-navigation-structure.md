# 02 · Navigation structure

## Source site

### Announcement bar
- Desktop: "Buy minimum of Rs 500 and get free shipping" + **Shop Now** button
- Mobile: "Buy minimum of 500rs and get a free shopping experience"
- Search field (AJAX product search) · phone `+91 800 729 1595`

### Header / primary menu
```
Home
About Us ▾
   ├─ Our Profile                           /about-naturalife/
   ├─ Team                                  /team/
   └─ Certification                         /certification/
Low GI ▾
   ├─ Glycemic Index                        /glycemic-index/
   ├─ Health Benefits of Low GI             /health-benefits-of-low-gi/
   └─ Low GI Diet Vs Other Diet             /low-gi-diet-vs-other-diet/
Our Products ▾
   ├─ Low GI Diet Sugar                     /low-gi-diet-sugar/
   ├─ Sugarlif® – Low Gi Whole Wheat Atta   /sugarlif-low-gi-whole-wheat-atta/
   └─ Other Applications                    /other-applications/
Contact Us                                  /contact-us/
Blog                                        /blog/
FAQ                                         /faq/
```
Right side: **My account** (opens a login / sign-up pop-up) · mini-cart with total and item count. Logo: `sugarlife-logo-1.svg`.

Live but not linked from the menu: `/sugarlif-low-gi-rice-atta/`, `/products/`, `/wishlist/`.

### Footer
| Column | Content |
|---|---|
| Brand | White logo · "Natural Life Speciality Pvt. Ltd. is a premier food company pioneering the Low GI food revolution. The company manufactures and sells Low GI food products in the brand name 'Sugarlif." · "Visit CKN Innovations Website" → `https://ckninnovations.com/` (button "Innovators Website") |
| Quick Links | Home · About Us · Our Products · Certificate · FAQ · Contact Us · Blogs |
| Shop | Sugarlif - Low GI Wheat Flour (Atta) · Sugarlif-Honey · Sugarlif - Low GI Diet Sugar |
| Newsletter | "Sign up for our newsletter — Be the first to know about our special offers, news, and updates." (e-mail field + Submit) |
| We Are Available On | Flipkart `https://dl.flipkart.com/s/PBFSAyuuuN` · Amazon `https://www.amazon.in/dp/B07BQP6C4Z/ref=cm_sw_r_apan_glt_i_35H5YC8JNTSHMXSJ0PKD` · JioMart `https://www.jiomart.com/catalogsearch/result?q=sugarlif` · 1mg `https://www.1mg.com/search/all?name=sugarlif` |
| Bottom bar | "© 2023. Natural Life Speciality \| All rights reserved." · Refund and Returns Policy · Privacy Policy |

No social-media profile links exist anywhere on the source site.

## New navigation (implemented; every item editable in Admin → Navigation)
```
Home
About Us ▾            Our Profile · Team · Certifications
Shop ▾ (mega menu)    All products · categories · featured products
Low GI Education ▾    Understand:      Glycemic Index · Health Benefits of Low GI · Low GI Diet vs Other Diet
                      Our innovations: Low GI Diet Sugar · Low GI Whole Wheat Atta · Low GI Rice · Other Applications
Blog
Contact
[Search] [My Account] [Wishlist] [Cart]
```
FAQ moves to the utility bar and footer; the Low GI Rice page, orphaned on the source, is now reachable from the menu.
