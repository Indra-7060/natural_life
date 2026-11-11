<?php

namespace App\Database\Seeds;

use App\Libraries\MediaLibrary;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * CMS pages and their sections. All copy is the client's own text from natural-life.in
 * (see docs/source-crawl/text/), re-arranged into the new page structure. Long-form
 * text (Our Story, policies) is read from data/source.json; short lists are inline.
 */
class PagesSeeder extends Seeder
{
    private MediaLibrary $media;
    private string $archive;
    private int $pageId = 0;
    private int $order  = 0;

    public function run(): void
    {
        helper('site');
        if ($this->db->table('pages')->countAllResults() > 0) {
            CLI::write('Pages already present - page seeding skipped.', 'yellow');

            return;
        }
        $src           = json_decode(file_get_contents(__DIR__ . '/data/source.json'), true);
        $this->media   = new MediaLibrary();
        $this->archive = ROOTPATH . 'docs/source-crawl/media/';
        $list          = static fn (array $texts) => array_map(static fn ($t) => ['text' => $t], $texts);

        // =============================================================== HOME
        $this->page('home', 'Home', 'home', null, null, null, 'Sugarlif — Natural Low GI Sugar, Atta, Honey & Jaggery',
            'Sugarlif Low GI foods are made by a revolutionary yet traditional method where natural sugar, wheat flour (atta), rice and others are organically re-engineered to be Low GI.', true);

        $this->sec('proof', 'Proof strip (four facts under the hero)', ['items' => [
            ['title' => 'GI Labs, Canada', 'text' => 'Certified Low GI'],
            ['title' => '10 years', 'text' => 'of research and development'],
            ['title' => 'BRC certified', 'text' => 'Global Standard for Food Safety'],
            ['title' => 'No after-taste', 'text' => 'No change in flavour'],
        ]]);
        $this->sec('featured', 'Featured collection', [
            'eyebrow' => 'For your healthy beginning', 'heading' => 'Everyday staples, re-engineered to be Low GI',
            'body' => '<p>Sugarlif® Low GI Food products are made from a revolutionary yet traditional method where Natural Sugar, Wheat Flour (Atta), Rice and others have been organically re-engineered to be made LOW GI.</p>',
            'cta_label' => 'Shop all products', 'cta_url' => '/shop',
        ]);
        $this->sec('categories', 'Shop by category', ['eyebrow' => 'Shop by category', 'heading' => 'Find your Low GI staple']);
        $this->sec('story', 'Our story', [
            'eyebrow' => 'Our story', 'heading' => 'It all began with one question…',
            'body' => '<blockquote>“Can you make my son eat sweets again?”</blockquote><p>It wasn’t a business proposal. It wasn’t the beginning of a company. It was a mother’s heartfelt plea to <strong>Dr. C. K. Nandagopalan</strong>, whose young son was living with juvenile diabetes.</p><p>Most people would have offered sympathy. Dr. Nandagopalan did something far more difficult—he decided to look for an answer. That single question became the spark that would eventually lead to Sugarlif.</p>',
            'image' => $this->img('2023/07/Sugar.png', 'products', 'Sugarlif Low GI Cane Sugar'), 'image_alt' => 'Sugarlif Low GI Cane Sugar pack beside a cup of tea',
            'cta_label' => 'Read our story', 'cta_url' => '/about-naturalife',
        ]);
        $this->sec('difference', 'What makes Sugarlif different', [
            'eyebrow' => 'Global disruption', 'heading' => 'What makes Sugarlif different?',
            'items' => $list([
                'Natural Life Speciality Pvt. Ltd. has the potential to convert regularly available natural Sugar, Wheat Flour (Atta) and Rice as Low GI foods',
                'Our Low GI process is scalable with all forms of natural cane sugar – Raw, Refined, Granular, Powdered and Liquid form',
                'A major advantage of Sugarlif® Low GI food products are that there is no change in flavour nor any bitter after-taste.',
                'Sugarlif® Low GI Sugar effectively replaces all artificial sweeteners with the huge benefit of having no dangerous side effects (as is the case with artificial sweeteners)',
            ]),
        ]);
        $this->sec('lowgi', 'Low GI education', [
            'eyebrow' => 'Low Glycemic Index (GI)', 'heading' => 'Diet food for your family',
            'body' => '<p>Sugarlif® Low GI Food products are made from a revolutionary yet traditional method where Natural Sugar, Wheat Flour (Atta), Rice and others have been organically re-engineered to be made LOW GI.</p>',
            'items' => [
                ['title' => 'What is Low Glycemic Index (GI)?', 'text' => 'The Glycemic Index is a number that determines how quickly the foods you eat containing carbohydrates raise your blood glucose levels.'],
                ['title' => 'What is Glycemic Load?', 'text' => 'The glycemic index (GI) is a measure of the blood glucose-raising potential of the carbohydrate content of a food compared to a reference food (generally pure glucose)'],
            ],
            'cta_label' => 'Understand the Glycemic Index', 'cta_url' => '/glycemic-index',
        ]);
        $this->sec('benefits', 'Benefits of Low GI foods', [
            'eyebrow' => 'Benefits of', 'heading' => 'Low GI Foods',
            'items' => $list(['Improves Glucose & Lipid Metabolism', 'Reduces the sudden rise in blood glucose', 'Brings down the risk of diabetes', 'Reduces the demand for insulin after food', 'Gives long lasting energy', 'Helps in weight management']),
            'cta_label' => 'See all health benefits', 'cta_url' => '/health-benefits-of-low-gi',
        ]);
        $this->sec('compare', 'Sugarlif vs High GI foods', [
            'eyebrow' => 'Side by side', 'heading' => 'Sugarlif® Low GI Foods and High GI Foods',
            'items' => [
                ['low' => 'Slows down the aging process', 'high' => 'Speeds up the aging process'],
                ['low' => 'Results in better Metabolism and Increased Immunity', 'high' => 'Suppresses Immunity'],
                ['low' => 'Maintains the Mineral Balance', 'high' => 'Disturbs the Mineral Balance'],
                ['low' => 'Maintains Cholesterol and Triglyceride levels', 'high' => 'Raises Cholesterol & Triglyceride levels'],
                ['low' => 'Helps in Weight Management', 'high' => 'Results in Weight Gain'],
                ['low' => 'Improves Glucose and Lipid Metabolism', 'high' => 'Obesity (Insulin Resistance)'],
                ['low' => 'Minimum or no Fluid retention', 'high' => 'Fluid Retention'],
                ['low' => 'Low demand of insulin post food', 'high' => 'High demand of insulin post food'],
            ],
        ]);
        $this->sec('research', 'Research and certification band', [
            'eyebrow' => 'Research & certification', 'heading' => 'Built on Quality. Trusted Around the World.',
            'body' => '<p>A good idea deserves good science. And good science deserves independent validation. Sugarlif’s Low GI technology has been evaluated by internationally recognised institutions, including GI Labs, Canada and the Whistler Center for Carbohydrate Research, USA, together with evaluations in Australia and India.</p>',
            'cta_label' => 'See our certifications', 'cta_url' => '/certification',
        ]);
        $this->sec('visual', 'Full-width visual', [
            'eyebrow' => 'The Sugarlif range', 'heading' => 'The foods people already love, a little better.',
            'image' => $this->img('2026/06/atta-banner.png', 'banners', 'Sugarlif Low GI Whole Wheat Atta'), 'image_alt' => 'Sugarlif Low GI Whole Wheat Atta',
            'cta_label' => 'Explore the range', 'cta_url' => '/shop',
        ]);
        $this->sec('bestsellers', 'Second product row', ['eyebrow' => 'More from the range', 'heading' => 'Sweets, sachets and jaggery']);
        $this->sec('testimonials', 'Testimonials', ['eyebrow' => 'Testimonials', 'heading' => 'In our customers’ words']);
        $this->sec('journal', 'Journal', ['eyebrow' => 'From the journal', 'heading' => 'Sugar, explained with a smile', 'cta_label' => 'All articles', 'cta_url' => '/blog']);
        $this->sec('faq', 'FAQ teaser', ['eyebrow' => 'Good to know', 'heading' => 'Frequently Asked Questions', 'cta_label' => 'Read all FAQs', 'cta_url' => '/faq']);

        // =============================================================== OUR PROFILE
        $this->page('about-naturalife', 'Our Profile', 'about', 'Natural Life Speciality Pvt. Ltd.', 'Our Story',
            null, 'Our Story — Natural Life Speciality Pvt. Ltd.', 'How a mother’s question to Dr. C. K. Nandagopalan led to ten years of research and to Sugarlif, real cane sugar with a lower Glycemic Index.');
        $this->sec('hero', 'Page opening (quote)', ['heading' => '“Can you make my son eat sweets again?”', 'body' => '<p>It was a mother’s heartfelt plea to Dr. C. K. Nandagopalan. That question still sits at the heart of everything we do.</p>']);
        foreach ($src['about']['story'] as $n => $chapter) {
            $this->sec('story-' . ($n + 1), 'Our Story — chapter ' . ($n + 1), ['heading' => $chapter['heading'], 'body' => trim($chapter['html'])]);
        }
        $journey = $src['about']['journey'];
        $quote   = array_shift($journey);
        $this->sec('journey', 'The Sugarlif Journey', [
            'eyebrow' => 'Our Journey', 'heading' => 'The Sugarlif Journey',
            'body' => '<blockquote>' . esc($quote) . '</blockquote>' . implode('', array_map(static fn ($p) => '<p>' . esc($p) . '</p>', $journey)),
        ]);
        $usps = $src['about']['usps'];
        $this->sec('usps', 'USPs — Global Disruption', [
            'eyebrow' => 'Sugarlif - Low GI Diet Sugar USPs', 'heading' => 'Global Disruption',
            'items' => array_map(static fn ($u) => ['text' => rtrim($u['text'], "\u{200B}")], $usps),
        ]);

        // =============================================================== TEAM
        $this->page('team', 'Team', 'team', 'Natural Life Speciality Pvt. Ltd.', 'The people behind Sugarlif', null, 'Team — Sugarlif',
            'Meet the leadership of Natural Life Speciality Pvt. Ltd.: Chairman Malik A Mulla, CEO Ajay K George and the core team behind Sugarlif.');

        // =============================================================== CERTIFICATION
        $this->page('certification', 'Certifications', 'certifications', 'Independent validation', 'A good idea deserves good science. And good science deserves independent validation.',
            null, 'Certifications — Sugarlif', 'Sugarlif’s Low GI technology has been evaluated by GI Labs, Canada and the Whistler Center for Carbohydrate Research, USA. Certificates and detailed reports are available on request.');
        $this->sec('request', 'Request certificates', [
            'heading' => 'For Certificates and detailed reports, please mail us at', 'body' => '<p>info@natural-life.in</p>',
            'cta_label' => 'Email us', 'cta_url' => 'mailto:info@natural-life.in',
        ]);

        // =============================================================== GLYCEMIC INDEX
        $this->page('glycemic-index', 'Glycemic Index', 'glycemic-index', 'Low GI Education', 'What is Glycemic Index', null, 'What is the Glycemic Index (GI)? — Sugarlif',
            'The Glycemic Index is a number that determines how quickly the foods you eat containing carbohydrates raise your blood glucose levels. Learn about GI, Glycemic Load and why low GI matters.');
        $this->sec('what', 'What is Glycemic Index?', [
            'eyebrow' => 'Glycemic Index', 'heading' => 'What is Glycemic Index?',
            'body' => '<p>The Glycemic Index is a number that determines how quickly the foods you eat containing carbohydrates raise your blood glucose levels.</p><p>The Glycemic Index (GI) is the ranking system for carbohydrates and how they affect your blood sugar levels.</p>',
        ]);
        $this->sec('scale', 'GI classification', [
            'heading' => 'Foods that contain Carbohydrates are classified as',
            'body' => '<p>These values are relative to Pure Glucose, which is 100</p>',
            'items' => [['title' => 'Low GI', 'text' => 'Below 55'], ['title' => 'Moderate GI', 'text' => 'Between 56-69'], ['title' => 'High GI', 'text' => 'Above 70']],
        ]);
        $this->sec('affects', 'How GI affects you', [
            'eyebrow' => 'GI Affects', 'heading' => 'How GI Affects You',
            'body' => '<p>The consumption of high-GI foods causes a sharp spike in blood glucose levels which is harmful for health and the main cause of diabetes and other ailments.</p><p>Consumption of low-GI foods results in slow increase and slow decline in blood sugar levels without any spike. Foods with low GI release glucose into the blood stream at a slow, sustainable rate and have proven health benefits.</p>',
        ]);
        $this->sec('load-link', 'GI is linked to Glycemic Load', [
            'eyebrow' => 'Glycemic Index', 'heading' => 'Glycemic Index is Linked to Glycemic Load',
            'body' => '<p>While Glycemic Index measures the QUALITY of the carbohydrates, (foods that release glucose into our bloodstream) contained in our food, the Glycemic Load in the same food measures the QUANTITY of carbohydrates.</p><p>Thus these two aspects tell us the Quality + Quantity of glucose that we consume.</p><p>The Glycemic Load is a more accurate way to approach carbohydrates because it not only considers a food’s glycemic index, but also the amount of carbohydrates per serving. For example, watermelon has a high Glycemic Index, but is low in carbohydrates, which means it has a low Glycemic Load.</p>',
        ]);
        $this->sec('gi-vs-gl', 'GI and GL compared', [
            'eyebrow' => 'Glycemic Index', 'heading' => 'Glycemic Index and Glycemic Load',
            'items' => [
                ['title' => 'GI', 'text' => 'GI shows how food affects blood suger levels'],
                ['title' => 'GL', 'text' => 'GL score is measured by the amount of carbohydrate intake'],
                ['title' => 'GI', 'text' => 'GI does not consider the actual size of the serving of food'],
                ['title' => 'GL', 'text' => 'GL load takes into account the serving size of the food'],
            ],
            'image' => $this->img('2024/07/Difference-Between-Glycaemic-Index-and-Glycaemic-Load.jpg', 'pages', 'Difference between Glycaemic Index and Glycaemic Load'),
            'image_alt' => 'Difference between Glycaemic Index and Glycaemic Load',
        ]);
        $this->sec('why', 'Why Low GI foods', [
            'eyebrow' => 'Glycemic Index', 'heading' => 'Why Low GI foods should become a part of our family’s food choices',
            'body' => '<p>A low-glycemic diet can help you control your weight by minimizing spikes in your blood sugar and insulin levels. This is particularly important if you have Type 2 Diabetes or at risk of developing it.</p><p>Studies have shown that the low GI diet can result in weight loss, reduce blood sugar levels, and lower the risk of heart disease and Type 2 Diabetes.</p><p>For anyone who is focusing on a healthier lifestyle for their families or to build up on their general health – there is no better alternate than a Low Gi Diet.</p>',
        ]);
        $this->sec('load', 'What is Glycemic Load?', [
            'eyebrow' => 'Glycemic Load', 'heading' => 'What is Glycemic Load?',
            'body' => '<p>The glycemic index (GI) is a measure of the blood glucose-raising potential of the carbohydrate content of a food compared to a reference food (generally pure glucose). Carbohydrate-containing foods can be classified as high- (≥70), moderate- (56-69), or low-GI (≤55) relative to pure glucose (GI=100).</p><p>Consumption of high-GI foods causes a sharp increase in postprandial blood glucose concentration that declines rapidly, whereas consumption of low-GI foods results in a lower blood glucose concentration that declines gradually.</p><p>The glycemic load (GL) is obtained by multiplying the quality of carbohydrate in a given food (GI) by the amount of carbohydrate in a serving of that food.</p>',
        ]);

        // =============================================================== HEALTH BENEFITS
        $this->page('health-benefits-of-low-gi', 'Health Benefits of Low GI', 'health-benefits', 'Low GI Education', 'Health Benefits of Low GI Foods', null, 'Health Benefits of Low GI Foods — Sugarlif',
            'Consuming Low GI foods is a simple and effective way to manage weight, fuel the body for sustained energy and reduce the demand for insulin post food.');
        $this->sec('benefits', 'Benefit list', [
            'eyebrow' => 'Low GI Foods', 'heading' => 'Health Benefits of Low GI Foods', 'body' => '<p>Consuming Low GI Foods is a simple and effective way to</p>',
            'items' => $list([
                'Manage weight by controlling appetite and delaying hunger', 'Fuel the body for sustained energy', 'Improve pregnancy outcomes',
                'Reduce the risk of developing type 2 diabetes and heart disease', 'Improve concentration', 'Increase physical performance by extending endurance',
                'Reduce breast cancer risk', 'Manage Polycystic Ovarian Syndrome (PCOS)', 'Decrease the risk of common diabetic complications',
                'Benefit eye health and prevent age-related macular degeneration (AMD), a leading cause of blindness', 'Reduce acne', 'Reduce the demand for insulin post food',
            ]),
        ]);
        $this->sec('versus', 'High versus Low GI foods', [
            'eyebrow' => 'Low GI Foods', 'heading' => 'High versus Low GI Foods',
            'body' => '<p>Consumption of high-GI foods causes a sharp increase in postprandial blood glucose concentration that declines rapidly, whereas consumption of low-GI foods results in a lower blood glucose concentration that declines gradually.</p>',
            'image' => $this->img('2024/07/gi-5.png', 'pages', 'High versus Low GI foods'), 'image_alt' => 'High versus Low GI foods',
        ]);
        $this->sec('mechanism', 'Mechanism of action', [
            'eyebrow' => 'Low Gi Diet', 'heading' => 'Low Gi Diet – Mechanism of Action',
            'body' => '<h3>Low GI diets and metabolic syndrome</h3><p>Low GI diets decrease insulin resistance and insulin levels, causing plasma glucose reduction, decrease free fatty acids levels, inflammation and endothelial dysfunction, causing an important reduction of cardiovascular risk factors.</p><p>Reduced hyperinsulinemia associated with a low-GI diet may reduce CVD risk through effects on oxidative stress, blood pressure, serum lipids, coagulation factors, inflammatory mediators, endothelial function and thrombolytic function</p>',
            'image' => $this->img('2024/07/gi-6.png', 'pages', 'Low GI diet mechanism of action'), 'image_alt' => 'Low GI diet — mechanism of action',
        ]);

        // =============================================================== DIET COMPARISON
        $this->page('low-gi-diet-vs-other-diet', 'Low GI Diet Vs Other Diet', 'diet-comparison', 'Low GI Education', 'Low GI Diet Vs Keto Diet', null, 'Low GI Diet vs Keto Diet — what a study found | Sugarlif',
            'Results and conclusions of a published study comparing a ketogenic diet with a personalised low-GI nutrigenetic diet over 24 weeks and an 18-month follow up.');
        $this->sec('results', 'Study results', [
            'eyebrow' => 'Low GI Diet Vs Keto Diet', 'heading' => 'Results',
            'body' => '<p>After 24 weeks, the keto group lost more weight: − 26.2 ± 3.1 kg vs − 23.5 ± 6.4 kg (p = 0.0061). However, at 18-month follow up, the subjects in the low-GI nutrigenetic diet had lost significantly more weight (− 27.5 ± 8.9 kg) than those in the ketogenic diet who had regained some weight (− 19.4 ± 5.0 kg) (p &lt; 0.0001). Additionally, after the 24-week diet and 18-month follow up the low-GI nutrigenetic diet group had significantly greater (p &lt; 0.0001) improvements in total cholesterol (ketogenic − 35.4 ± 32.2 mg/dl; low-GI nutrigenetic − 52.5 ± 24.3 mg/dl), HDL cholesterol (ketogenic + 4.7 ± 4.5 mg/dl; low-GI nutrigenetic + 11.9 ± 4.1 mg/dl), and fasting glucose (ketogenic − 13.7 ± 8.4 mg/dl; low-GI nutrigenetic − 24.7 ± 7.4 mg/dl).</p>',
            'items' => [
                ['measure' => 'Weight change after 24 weeks', 'keto' => '− 26.2 ± 3.1 kg', 'lowgi' => '− 23.5 ± 6.4 kg'],
                ['measure' => 'Weight change at 18-month follow up', 'keto' => '− 19.4 ± 5.0 kg', 'lowgi' => '− 27.5 ± 8.9 kg'],
                ['measure' => 'Total cholesterol', 'keto' => '− 35.4 ± 32.2 mg/dl', 'lowgi' => '− 52.5 ± 24.3 mg/dl'],
                ['measure' => 'HDL cholesterol', 'keto' => '+ 4.7 ± 4.5 mg/dl', 'lowgi' => '+ 11.9 ± 4.1 mg/dl'],
                ['measure' => 'Fasting glucose', 'keto' => '− 13.7 ± 8.4 mg/dl', 'lowgi' => '− 24.7 ± 7.4 mg/dl'],
            ],
            'image' => $this->img('2024/07/chart-keto.png', 'pages', 'Study chart: ketogenic vs low-GI nutrigenetic diet'), 'image_alt' => 'Chart from the study comparing the ketogenic and low-GI nutrigenetic diets',
        ]);
        $this->sec('conclusions', 'Study conclusions', [
            'eyebrow' => 'Low GI Diet Vs Keto Diet', 'heading' => 'Conclusions',
            'body' => '<p>These findings demonstrate that the ketogenic group experienced enhanced weight loss during the 24-week dietary intervention. However, at 18-month follow up, the personalised nutrition group (low GI/NG) lost significantly more weight and experienced significantly greater improvements in measures of cholesterol and blood glucose. This suggests that personalising nutrition has the potential to enhance long-term weight loss and changes in cardiometabolic parameters.</p>',
        ]);
        $this->sec('reference', 'Reference', [
            'heading' => 'Reference', 'cta_label' => 'BMC Nutrition — https://bmcnutr.biomedcentral.com/articles/10.1186/s40795-020-00370-7',
            'cta_url' => 'https://bmcnutr.biomedcentral.com/articles/10.1186/s40795-020-00370-7',
        ]);

        // =============================================================== LOW GI DIET SUGAR
        $this->page('low-gi-diet-sugar', 'Low GI Diet Sugar', 'product-story', 'What is Sugarlif Sugar?', 'Sugarlif® - Low GI Diet Sugar', null, 'Sugarlif® Low GI Diet Sugar — how it works',
            'Sugarlif is a first of its kind product where normal cane sugar has been treated to be made Low GI, tested at GI Labs in Canada and the Whistler Carbohydrate Research Centre, USA.');
        $this->sec('about', 'About the product', [
            'eyebrow' => 'What is Sugarlif Sugar?', 'heading' => 'Sugarlif® - Low GI Diet Sugar',
            'body' => '<p>“Sugarlif” is a first of its kind of product in the world- where in normal cane sugar has been treated to be made LOW GI.</p><p>Sugarlif is made by blending and treating a proprietary aqueous solution with regular cane sugar. The solution is aqueous and no chemicals or solvents are used in the extraction process. Active ingredients in the solutions have Glycemic Index lowering properties that not only keeps the blood sugar levels steady and help our body to metabolize fat more efficiently but also facilitates better absorption of glucose at the cellular level. Furthermore, Sugarlif has no side effects like any of the artificial sweeteners or sugar substitutes available in the market today including stevia.</p><p>As you know, a nutritious low Gi diet is for each and every one of us irrespective of age and gender. Anyone who is focusing on monitoring a healthier lifestyle for their families or eyeing to build up on the general health and looking to prevent or manage a specific health conditions like Diabetes, heart problem, weight issues – there is no better alternate than a Low Gi Diet</p>',
            'image' => $this->img('2023/07/Sugar-1-01.jpg', 'products', 'Sugarlif Low GI Sugar'), 'image_alt' => 'Sugarlif Low GI Diet Sugar',
        ]);
        $this->sec('testing', 'Testing and industry use', [
            'eyebrow' => 'Tested internationally', 'heading' => 'Validated at international labs',
            'body' => '<p>Sugarlif Low Gi Diet Sugar is tested at top international labs LIKE GI Labs in Canada, Whistler Carbohydrate Research Centre, USA and Australia for its Low Gi Properties and Glucose absorption capability.</p><p>For all the food processing industry Sugarlif gives all the beneficial properties of normal cane sugar like Taste, Colour and Flavour, Bulk and Texture, Fermentation and Preservation and at the same time Sugarlif sugar has addressed the major concern with respect to the Sugar i.e Glycemic Index and toxicity, thereby making Sugarlif a universal product</p>',
        ]);
        $this->sec('benefits', 'Health benefits of Sugarlif', [
            'eyebrow' => 'Benefits', 'heading' => 'Health Benefits of Sugarlif',
            'items' => $list(['Less sudden increase in blood glucose', 'Lower level of blood Lipids', 'More sustained supply of Glucose to the brain', 'Reduce demand for insulin post food', 'Helps in weight management', 'Better absorption of nutrients by body cells']),
        ]);
        $this->sec('outcomes', 'Resulting in…', [
            'eyebrow' => 'Sugarlif - Low GI Diet Sugar', 'heading' => 'Resulting in..',
            'items' => $list(['Lower risk of diabetes', 'Better concentration power & memory', 'Higher energy level', 'Healthier blood vessels', 'Supports active lifestyle', 'Reduce demand for insulin post food']),
        ]);
        $this->sec('compare-1', 'Sugarlif v/s Ordinary Sugar', [
            'eyebrow' => 'Benefits of Sugarlif', 'heading' => 'Sugarlif® v/s Ordinary Sugar',
            'items' => [
                ['a' => 'Sugarlif® (Natural Low GI Diet Sugar)', 'b' => 'Sugar (High GI)'],
                ['a' => 'Slows down the aging process', 'b' => 'Speeds up the aging process'],
                ['a' => 'Results in better Metabolism and Increased Immunity', 'b' => 'Suppresses Immunity'],
                ['a' => 'Maintains the Mineral Balance', 'b' => 'Disturbs the Mineral Balance'],
                ['a' => 'Maintains Cholesterol and Triglyceride levels', 'b' => 'Raises Cholesterol & Triglyceride levels'],
                ['a' => 'Helps in Weight Management', 'b' => 'Results in Weight Gain'],
                ['a' => 'Improves Glucose and Lipid Metabolism', 'b' => 'Obesity (Insulin Resistance)'],
                ['a' => 'Minimum or no Fluid retention', 'b' => 'Fluid Retention'],
            ],
        ]);
        $this->sec('compare-2', 'Sugarlif v/s Other Sweeteners', [
            'eyebrow' => 'Sugarlif compared to others', 'heading' => 'Sugarlif® v/s Other Sweeteners',
            'items' => [
                ['parameter' => 'Parameters', 'artificial' => 'Artificial Sweeteners', 'stevia' => 'Stevia Sugar Substitute', 'sugarlif' => 'Sugarlif® Natural Low GI Diet Sugar'],
                ['parameter' => 'Advisable for Kids', 'artificial' => 'No', 'stevia' => 'No', 'sugarlif' => 'Yes'],
                ['parameter' => 'Nutritional Value', 'artificial' => 'No', 'stevia' => 'Yes', 'sugarlif' => 'Yes'],
                ['parameter' => 'Weight Management', 'artificial' => 'Yes', 'stevia' => 'Yes', 'sugarlif' => 'Yes'],
                ['parameter' => 'Increased Energy Levels', 'artificial' => 'No', 'stevia' => 'No', 'sugarlif' => 'Yes'],
                ['parameter' => 'Harmful Side Effects', 'artificial' => 'Yes', 'stevia' => 'Yes', 'sugarlif' => 'No'],
                ['parameter' => 'Increased Immunity', 'artificial' => 'No', 'stevia' => 'Yes', 'sugarlif' => 'Yes'],
            ],
        ]);
        $this->sec('shop', 'Shop this product', ['eyebrow' => 'Buy Now', 'heading' => 'Bring Sugarlif Low GI Sugar home', 'cta_label' => 'Shop Low GI Sugar', 'cta_url' => '/product/sugarlif-low-gi-diet-sugar']);

        // =============================================================== WHOLE WHEAT ATTA
        $this->page('sugarlif-low-gi-whole-wheat-atta', 'Sugarlif® - Low GI Whole Wheat Atta', 'product-story', 'About Wheat Atta', 'Sugarlif® - Low GI Wheat Flour (Atta)', null, 'Sugarlif® Low GI Whole Wheat Atta — GI value 36',
            'Sugarlif Low GI whole wheat atta is made with 100% natural wheat grain and organically re-engineered to be Low GI. 100% vegan, high on fibre, with a GI value of 36.');
        $this->sec('about', 'About the product', [
            'eyebrow' => 'About Wheat Atta', 'heading' => 'Sugarlif® - Low GI Wheat Flour (Atta)',
            'body' => '<p>Sugarlif Low GI whole wheat atta is made with 100% natural wheat grain. Sugarlif Low GI whole wheat atta is organically re-engineered to be made Low GI by blending the wheat with a water based herbal extract.</p><p>It is 100% vegan, high on fiber and is a healthy food for whole family. With a GI value of 36, Sugarlif Low Gi whole wheat atta is must for every house hold</p>',
            'image' => $this->img('2023/07/Atta.png', 'products', 'Sugarlif Low GI Whole Wheat Atta'), 'image_alt' => 'Sugarlif Low GI Whole Wheat Flour (Atta)',
            'items' => [['title' => '36', 'text' => 'GI value'], ['title' => '100%', 'text' => 'natural wheat grain'], ['title' => '100%', 'text' => 'vegan']],
        ]);
        $this->sec('benefits', 'Benefits', [
            'eyebrow' => 'Benefits', 'heading' => 'Benefits of Sugarlif® Whole Wheat Atta',
            'items' => $list(['Improves Glucose and Lipid Metabolism', 'Long Lasting Energy', 'Helps in weight management', 'Lowers the risk of Diabetes', 'Safe for all age group', 'Improves Insulin Sensitivity', 'Higher Nutritional Value']),
        ]);
        $this->sec('shop', 'Shop this product', ['eyebrow' => 'Buy Now', 'heading' => 'Bring Sugarlif Low GI Atta home', 'cta_label' => 'Shop Low GI Atta', 'cta_url' => '/product/sugarlif-low-gi-wheat-flour-atta']);

        // =============================================================== LOW GI RICE
        $this->page('sugarlif-low-gi-rice-atta', 'Sugarlif® - Low GI Rice', 'product-story', 'About Rice', 'Sugarlif® - Low GI Rice', null, 'Sugarlif® Low GI Rice — GI of 40',
            'The GI of normal rice is about 69. Sugarlif Low GI rice has a GI of just 40 and is available in five varieties, from daily-use rice to Basmati.');
        $this->sec('about', 'About the product', [
            'eyebrow' => 'About Rice', 'heading' => 'Sugarlif® - Low GI Rice',
            'body' => '<p>Rice is one of the most vital ingredients in our daily food consumption and can become a challenge for rice lovers facing pre-diabtes or diabetes. The GI of normal rice is about 69 (which is high). Sugarlif Low GI rice has a GI of just 40, making it safe and suitable for health diabetics and health conscious people.</p><p>Sugarlif Low GI Rice is available in 5 varieties ranging from regular daily use rice to exclusive special occasion Basmati rice.</p>',
            'image' => $this->img('2024/07/SugarlifRice-about.png', 'pages', 'Sugarlif Low GI Rice'), 'image_alt' => 'Sugarlif Low GI Rice',
            'items' => [['title' => '40', 'text' => 'GI of Sugarlif Low GI rice'], ['title' => '69', 'text' => 'GI of normal rice (about)'], ['title' => '5', 'text' => 'varieties']],
        ]);
        $this->sec('varieties', 'Rice varieties', [
            'eyebrow' => 'Varieties', 'heading' => 'Available in 5 varieties',
            'items' => $list(['HMT Kolam', 'Surti Kolam', 'Basmati Rozana', '1121 Golden Sella', 'Two-year aged Basmati rice']),
        ]);
        $this->sec('benefits', 'Benefits', [
            'eyebrow' => 'Benefits', 'heading' => 'Benefits of Sugarlif® Rice',
            'items' => $list(['Safe for Diabetics', 'Brings down the risk of diabetes', 'Rejuvenates mind and body', 'Delays hunger pangs', 'It reduces the sudden rise in blood glucose', 'Improves memory and concentration', 'Helps in weight management', 'Gives long lasting energy']),
        ]);
        $this->sec('shop', 'Shop call-to-action', ['eyebrow' => 'Buy Now', 'heading' => 'Coming Soon', 'body' => '<p>Sugarlif Low GI Rice is not yet available in our online store.</p>', 'cta_label' => 'Visit Our Store', 'cta_url' => '/shop']);

        // =============================================================== OTHER APPLICATIONS
        $this->page('other-applications', 'Other Applications', 'applications', 'Other Applications', 'Making Carbohydrates Digestive Friendly', null, 'Other Applications of Sugarlif Low GI technology',
            'From the first Low GI Honey in the world to jams, sauces, ice creams and Low GI bread — where Sugarlif’s Low GI technology is applied.');
        $this->sec('applications', 'Application list', [
            'eyebrow' => 'Other Applications', 'heading' => 'Making Carbohydrates Digestive Friendly',
            'items' => $list([
                'The company has already developed the first Low GI Honey in the world.',
                'Products made with Sugarlif® include Jams, Ketchups, Sauces, Milkshakes, Ice Creams, Soft Drinks, Cakes, Cookies, Nutraceuticals … the list goes on',
                'The company has developed Low GI Jaggery in a granule form',
                'The technology can be used to produce Low GI Rice, Wheat and other grains. Making Low GI Bread is now a reality',
            ]),
            'image' => $this->img('2023/07/Honey.png', 'products', 'Sugarlif Low GI Honey'), 'image_alt' => 'Sugarlif Low GI Forest Honey',
        ]);
        $this->sec('shop', 'Shop call-to-action', ['eyebrow' => 'Buy Now', 'heading' => 'Visit Our Store', 'cta_label' => 'Shop all products', 'cta_url' => '/shop']);

        // =============================================================== FAQ
        $this->page('faq', 'FAQ', 'faq', 'FAQs', '“Sugarlif” – A Low GI Revolution', null, 'Frequently Asked Questions — Sugarlif',
            'Answers about the Glycemic Index, how Sugarlif Low GI Diet Sugar is made, who it is for and how to use it.');

        // =============================================================== CONTACT
        $this->page('contact-us', 'Contact Us', 'contact', 'Contact Us', 'Get in Touch', '<p>If you need to order in bulk, please don’t hesitate to contact us on +918007291595 via call or WhatsApp.</p>', 'Contact Sugarlif — Natural Life Speciality Pvt. Ltd.',
            'Contact Natural Life Speciality Pvt. Ltd., F-7, Sacred World Mall, Wanwadi, Pune 411040. Call or WhatsApp +91 800 729 1595 or email info@natural-life.in.');

        // =============================================================== LEGAL
        $this->page('privacy-policy', 'Privacy Policy', 'legal', 'Legal', null, $src['legal']['privacy'], 'Privacy Policy — Sugarlif', 'How Sugarlif collects, uses and protects the information you provide when you visit our website or use our services.');
        $this->page('refund_returns', 'Refund and Returns Policy', 'legal', 'Legal', null, $src['legal']['refund'], 'Refund, Returns and Shipping Policy — Sugarlif', 'Sugarlif’s refund and returns policy and the shipping policy for orders delivered within India.');
        $this->page('terms-and-conditions', 'Terms and Conditions', 'legal', 'Legal', null,
            '<p>The Terms and Conditions for this website are being finalised and will be published on this page.</p><p>In the meantime, please refer to our <a href="/privacy-policy">Privacy Policy</a> and <a href="/refund_returns">Refund and Returns Policy</a>, or <a href="/contact-us">contact us</a> with any questions.</p>',
            'Terms and Conditions — Sugarlif', 'Terms and Conditions for purchases from Sugarlif.');

        CLI::write($this->db->table('pages')->countAllResults() . ' pages and ' . $this->db->table('page_sections')->countAllResults() . ' page sections imported.', 'green');
    }

    private function page(string $slug, string $title, string $template, ?string $eyebrow, ?string $intro, ?string $body, string $metaTitle, string $metaDesc, bool $system = false): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('pages')->insert([
            'slug' => $slug, 'title' => $title, 'template' => $template, 'eyebrow' => $eyebrow, 'intro' => $intro, 'body' => $body,
            'is_published' => 1, 'is_system' => $system ? 1 : 0, 'meta_title' => $metaTitle, 'meta_description' => $metaDesc,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->pageId = $this->db->insertID();
        $this->order  = 0;
    }

    private function sec(string $key, string $label, array $d): void
    {
        $this->db->table('page_sections')->insert([
            'page_id' => $this->pageId, 'section_key' => $key, 'admin_label' => $label,
            'eyebrow' => $d['eyebrow'] ?? null, 'heading' => $d['heading'] ?? null, 'body' => $d['body'] ?? null,
            'image' => $d['image'] ?? null, 'image_alt' => $d['image_alt'] ?? null,
            'cta_label' => $d['cta_label'] ?? null, 'cta_url' => $d['cta_url'] ?? null,
            'items' => isset($d['items']) ? json_encode($d['items'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'is_active' => 1, 'sort_order' => ++$this->order, 'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function img(string $rel, string $folder, ?string $alt = null): ?string
    {
        $m = $this->media->import($this->archive . $rel, $folder, $alt, 'https://natural-life.in/wp-content/uploads/' . $rel);

        return $m['path'] ?? null;
    }
}
