<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\TaxClass;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductSeeder extends Seeder
{
    /**
     * Indian FMCG / kirana-style catalog (50+ SKUs) for demo / local development.
     *
     * Keys:
     * - category: CategorySeeder slug
     * - brand: BrandSeeder slug
     * - tax: TaxClass name (GST 0% / 5% / 12% / 18%)
     *
     * @return list<array{
     *     upc: string,
     *     sku: string,
     *     name: string,
     *     description: string,
     *     category: string,
     *     brand: string,
     *     tax: string,
     *     mrp: float,
     *     selling_price: float,
     *     min_stock_level: float,
     *     unit: string,
     *     stock_quantity: float
     * }>
     */
    public static function catalog(): array
    {
        return [
            // —— Amul (Dairy) ——
            ['upc' => '8901262200019', 'sku' => 'AMUL-MILK-500', 'name' => 'Amul Taaza Toned Milk 500 ml', 'description' => 'Fresh toned milk pouch.', 'category' => 'grocery-staples', 'brand' => 'amul', 'tax' => 'GST 5%', 'mrp' => 28.00, 'selling_price' => 27.00, 'min_stock_level' => 40, 'unit' => 'pcs', 'stock_quantity' => 120],
            ['upc' => '8901262200026', 'sku' => 'AMUL-MILK-1L', 'name' => 'Amul Taaza Toned Milk 1 L', 'description' => 'Fresh toned milk pouch.', 'category' => 'grocery-staples', 'brand' => 'amul', 'tax' => 'GST 5%', 'mrp' => 56.00, 'selling_price' => 54.00, 'min_stock_level' => 30, 'unit' => 'pcs', 'stock_quantity' => 90],
            ['upc' => '8901262101015', 'sku' => 'AMUL-BUTTER-100', 'name' => 'Amul Butter 100 g', 'description' => 'Pasteurised butter.', 'category' => 'grocery-staples', 'brand' => 'amul', 'tax' => 'GST 12%', 'mrp' => 58.00, 'selling_price' => 56.00, 'min_stock_level' => 25, 'unit' => 'pcs', 'stock_quantity' => 75],
            ['upc' => '8901262101022', 'sku' => 'AMUL-BUTTER-500', 'name' => 'Amul Butter 500 g', 'description' => 'Pasteurised butter pack.', 'category' => 'grocery-staples', 'brand' => 'amul', 'tax' => 'GST 12%', 'mrp' => 285.00, 'selling_price' => 275.00, 'min_stock_level' => 15, 'unit' => 'pcs', 'stock_quantity' => 40],
            ['upc' => '8901262220018', 'sku' => 'AMUL-CHEESE-200', 'name' => 'Amul Cheese Slices 200 g', 'description' => 'Processed cheese slices.', 'category' => 'grocery-staples', 'brand' => 'amul', 'tax' => 'GST 12%', 'mrp' => 135.00, 'selling_price' => 129.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 55],
            ['upc' => '8901262240016', 'sku' => 'AMUL-GHEE-1L', 'name' => 'Amul Pure Ghee 1 L', 'description' => 'Cow ghee tin.', 'category' => 'grocery-staples', 'brand' => 'amul', 'tax' => 'GST 12%', 'mrp' => 620.00, 'selling_price' => 599.00, 'min_stock_level' => 10, 'unit' => 'pcs', 'stock_quantity' => 28],
            ['upc' => '8901262305014', 'sku' => 'AMUL-LASSI-200', 'name' => 'Amul Masti Lassi 200 ml', 'description' => 'Sweet lassi tetra pack.', 'category' => 'snacks-beverages', 'brand' => 'amul', 'tax' => 'GST 12%', 'mrp' => 20.00, 'selling_price' => 18.00, 'min_stock_level' => 40, 'unit' => 'pcs', 'stock_quantity' => 100],
            ['upc' => '8901262305021', 'sku' => 'AMUL-BUTTERMILK', 'name' => 'Amul Buttermilk 200 ml', 'description' => 'Spiced buttermilk.', 'category' => 'snacks-beverages', 'brand' => 'amul', 'tax' => 'GST 12%', 'mrp' => 12.00, 'selling_price' => 10.00, 'min_stock_level' => 40, 'unit' => 'pcs', 'stock_quantity' => 110],
            ['upc' => '8901262401013', 'sku' => 'AMUL-ICECREAM-VAN', 'name' => 'Amul Vanilla Ice Cream 1 L', 'description' => 'Vanilla ice cream tub.', 'category' => 'snacks-beverages', 'brand' => 'amul', 'tax' => 'GST 18%', 'mrp' => 210.00, 'selling_price' => 199.00, 'min_stock_level' => 12, 'unit' => 'pcs', 'stock_quantity' => 32],

            // —— Britannia ——
            ['upc' => '8901063010017', 'sku' => 'BRIT-GOODDAY-200', 'name' => 'Britannia Good Day Cashew 200 g', 'description' => 'Cashew butter cookies.', 'category' => 'snacks-beverages', 'brand' => 'britannia', 'tax' => 'GST 18%', 'mrp' => 40.00, 'selling_price' => 38.00, 'min_stock_level' => 30, 'unit' => 'pcs', 'stock_quantity' => 80],
            ['upc' => '8901063010024', 'sku' => 'BRIT-GOODDAY-600', 'name' => 'Britannia Good Day Cashew 600 g', 'description' => 'Family pack cashew cookies.', 'category' => 'snacks-beverages', 'brand' => 'britannia', 'tax' => 'GST 18%', 'mrp' => 120.00, 'selling_price' => 112.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 45],
            ['upc' => '8901063020016', 'sku' => 'BRIT-BOURBON-150', 'name' => 'Britannia Bourbon 150 g', 'description' => 'Chocolate cream biscuits.', 'category' => 'snacks-beverages', 'brand' => 'britannia', 'tax' => 'GST 18%', 'mrp' => 30.00, 'selling_price' => 28.00, 'min_stock_level' => 35, 'unit' => 'pcs', 'stock_quantity' => 95],
            ['upc' => '8901063030015', 'sku' => 'BRIT-MARIE-250', 'name' => 'Britannia Marie Gold 250 g', 'description' => 'Light tea-time biscuits.', 'category' => 'snacks-beverages', 'brand' => 'britannia', 'tax' => 'GST 18%', 'mrp' => 35.00, 'selling_price' => 33.00, 'min_stock_level' => 30, 'unit' => 'pcs', 'stock_quantity' => 70],
            ['upc' => '8901063040014', 'sku' => 'BRIT-TOAST-400', 'name' => 'Britannia Premium Bake Rusk 400 g', 'description' => 'Suji rusk toast.', 'category' => 'snacks-beverages', 'brand' => 'britannia', 'tax' => 'GST 18%', 'mrp' => 70.00, 'selling_price' => 65.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 48],
            ['upc' => '8901063050013', 'sku' => 'BRIT-BREAD-400', 'name' => 'Britannia Whole Wheat Bread 400 g', 'description' => 'Atta bread loaf.', 'category' => 'grocery-staples', 'brand' => 'britannia', 'tax' => 'GST 5%', 'mrp' => 45.00, 'selling_price' => 42.00, 'min_stock_level' => 25, 'unit' => 'pcs', 'stock_quantity' => 60],
            ['upc' => '8901063060012', 'sku' => 'BRIT-CHEESE-180', 'name' => 'Britannia Cheese Spread Classic 180 g', 'description' => 'Processed cheese spread.', 'category' => 'grocery-staples', 'brand' => 'britannia', 'tax' => 'GST 12%', 'mrp' => 95.00, 'selling_price' => 89.00, 'min_stock_level' => 15, 'unit' => 'pcs', 'stock_quantity' => 36],
            ['upc' => '8901063070011', 'sku' => 'BRIT-CAKE-45', 'name' => 'Britannia Fruit Cake 45 g', 'description' => 'Single-serve fruit cake.', 'category' => 'snacks-beverages', 'brand' => 'britannia', 'tax' => 'GST 18%', 'mrp' => 15.00, 'selling_price' => 14.00, 'min_stock_level' => 40, 'unit' => 'pcs', 'stock_quantity' => 120],

            // —— Parle ——
            ['upc' => '8901719100015', 'sku' => 'PARLE-G-800', 'name' => 'Parle-G Glucose Biscuits 800 g', 'description' => 'Classic glucose biscuits jar.', 'category' => 'snacks-beverages', 'brand' => 'parle', 'tax' => 'GST 18%', 'mrp' => 100.00, 'selling_price' => 95.00, 'min_stock_level' => 25, 'unit' => 'pcs', 'stock_quantity' => 65],
            ['upc' => '8901719100022', 'sku' => 'PARLE-G-250', 'name' => 'Parle-G Glucose Biscuits 250 g', 'description' => 'Classic glucose biscuits pack.', 'category' => 'snacks-beverages', 'brand' => 'parle', 'tax' => 'GST 18%', 'mrp' => 30.00, 'selling_price' => 28.00, 'min_stock_level' => 40, 'unit' => 'pcs', 'stock_quantity' => 140],
            ['upc' => '8901719110014', 'sku' => 'PARLE-MONACO-200', 'name' => 'Parle Monaco Classic 200 g', 'description' => 'Salted crackers.', 'category' => 'snacks-beverages', 'brand' => 'parle', 'tax' => 'GST 18%', 'mrp' => 30.00, 'selling_price' => 28.00, 'min_stock_level' => 30, 'unit' => 'pcs', 'stock_quantity' => 85],
            ['upc' => '8901719120013', 'sku' => 'PARLE-KRACKJACK', 'name' => 'Parle Krackjack 400 g', 'description' => 'Sweet & salty biscuits.', 'category' => 'snacks-beverages', 'brand' => 'parle', 'tax' => 'GST 18%', 'mrp' => 60.00, 'selling_price' => 55.00, 'min_stock_level' => 25, 'unit' => 'pcs', 'stock_quantity' => 70],
            ['upc' => '8901719130012', 'sku' => 'PARLE-HIDESEEK', 'name' => 'Parle Hide & Seek Chocolate Chip 200 g', 'description' => 'Chocolate chip cookies.', 'category' => 'snacks-beverages', 'brand' => 'parle', 'tax' => 'GST 18%', 'mrp' => 40.00, 'selling_price' => 38.00, 'min_stock_level' => 30, 'unit' => 'pcs', 'stock_quantity' => 78],
            ['upc' => '8901719140011', 'sku' => 'PARLE-20-20', 'name' => 'Parle 20-20 Butter Cookies 150 g', 'description' => 'Butter cookies.', 'category' => 'snacks-beverages', 'brand' => 'parle', 'tax' => 'GST 18%', 'mrp' => 20.00, 'selling_price' => 18.00, 'min_stock_level' => 35, 'unit' => 'pcs', 'stock_quantity' => 90],
            ['upc' => '8901719150010', 'sku' => 'PARLE-MELODY', 'name' => 'Parle Melody Chocolate 261 g', 'description' => 'Toffee jar.', 'category' => 'snacks-beverages', 'brand' => 'parle', 'tax' => 'GST 18%', 'mrp' => 100.00, 'selling_price' => 95.00, 'min_stock_level' => 15, 'unit' => 'pcs', 'stock_quantity' => 40],
            ['upc' => '8901719160019', 'sku' => 'PARLE-MANGO', 'name' => 'Parle Mango Bite 261 g', 'description' => 'Mango toffee jar.', 'category' => 'snacks-beverages', 'brand' => 'parle', 'tax' => 'GST 18%', 'mrp' => 100.00, 'selling_price' => 95.00, 'min_stock_level' => 15, 'unit' => 'pcs', 'stock_quantity' => 38],

            // —— Nestlé ——
            ['upc' => '8901058000014', 'sku' => 'NEST-MAGGI-70', 'name' => 'Maggi 2-Minute Noodles Masala 70 g', 'description' => 'Instant noodles single pack.', 'category' => 'grocery-staples', 'brand' => 'nestle', 'tax' => 'GST 18%', 'mrp' => 14.00, 'selling_price' => 12.00, 'min_stock_level' => 80, 'unit' => 'pcs', 'stock_quantity' => 250],
            ['upc' => '8901058000021', 'sku' => 'NEST-MAGGI-560', 'name' => 'Maggi 2-Minute Noodles Masala 8-Pack', 'description' => 'Family pack instant noodles.', 'category' => 'grocery-staples', 'brand' => 'nestle', 'tax' => 'GST 18%', 'mrp' => 112.00, 'selling_price' => 105.00, 'min_stock_level' => 30, 'unit' => 'pcs', 'stock_quantity' => 70],
            ['upc' => '8901058010013', 'sku' => 'NEST-NESCAFE-50', 'name' => 'Nescafé Classic Coffee 50 g', 'description' => 'Instant coffee jar.', 'category' => 'snacks-beverages', 'brand' => 'nestle', 'tax' => 'GST 18%', 'mrp' => 165.00, 'selling_price' => 155.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 45],
            ['upc' => '8901058010020', 'sku' => 'NEST-NESCAFE-100', 'name' => 'Nescafé Classic Coffee 100 g', 'description' => 'Instant coffee jar.', 'category' => 'snacks-beverages', 'brand' => 'nestle', 'tax' => 'GST 18%', 'mrp' => 320.00, 'selling_price' => 305.00, 'min_stock_level' => 15, 'unit' => 'pcs', 'stock_quantity' => 32],
            ['upc' => '8901058020012', 'sku' => 'NEST-EVERYDAY-400', 'name' => 'Nestlé Everyday Dairy Whitener 400 g', 'description' => 'Dairy whitener for tea/coffee.', 'category' => 'grocery-staples', 'brand' => 'nestle', 'tax' => 'GST 5%', 'mrp' => 225.00, 'selling_price' => 215.00, 'min_stock_level' => 18, 'unit' => 'pcs', 'stock_quantity' => 40],
            ['upc' => '8901058030011', 'sku' => 'NEST-MILO-400', 'name' => 'Milo Activ-Go Chocolate Malt 400 g', 'description' => 'Chocolate malt drink powder.', 'category' => 'snacks-beverages', 'brand' => 'nestle', 'tax' => 'GST 18%', 'mrp' => 245.00, 'selling_price' => 235.00, 'min_stock_level' => 12, 'unit' => 'pcs', 'stock_quantity' => 28],
            ['upc' => '8901058040010', 'sku' => 'NEST-KITKAT-4F', 'name' => 'KitKat 4 Finger 36.5 g', 'description' => 'Wafer chocolate bar.', 'category' => 'snacks-beverages', 'brand' => 'nestle', 'tax' => 'GST 18%', 'mrp' => 30.00, 'selling_price' => 28.00, 'min_stock_level' => 50, 'unit' => 'pcs', 'stock_quantity' => 160],
            ['upc' => '8901058050019', 'sku' => 'NEST-MUNCH-20', 'name' => 'Munch Chocolate 20.8 g', 'description' => 'Crispy wafer chocolate.', 'category' => 'snacks-beverages', 'brand' => 'nestle', 'tax' => 'GST 18%', 'mrp' => 10.00, 'selling_price' => 9.00, 'min_stock_level' => 60, 'unit' => 'pcs', 'stock_quantity' => 200],
            ['upc' => '8901058060018', 'sku' => 'NEST-CERELAC-300', 'name' => 'Cerelac Wheat Apple 300 g', 'description' => 'Infant cereal.', 'category' => 'grocery-staples', 'brand' => 'nestle', 'tax' => 'GST 5%', 'mrp' => 265.00, 'selling_price' => 255.00, 'min_stock_level' => 10, 'unit' => 'pcs', 'stock_quantity' => 24],

            // —— ITC ——
            ['upc' => '8901030800016', 'sku' => 'ITC-AASH-ATTA-5', 'name' => 'Aashirvaad Whole Wheat Atta 5 kg', 'description' => 'Chakki fresh atta.', 'category' => 'grocery-staples', 'brand' => 'itc', 'tax' => 'GST 5%', 'mrp' => 295.00, 'selling_price' => 279.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 55],
            ['upc' => '8901030800023', 'sku' => 'ITC-AASH-ATTA-10', 'name' => 'Aashirvaad Whole Wheat Atta 10 kg', 'description' => 'Chakki fresh atta family pack.', 'category' => 'grocery-staples', 'brand' => 'itc', 'tax' => 'GST 5%', 'mrp' => 560.00, 'selling_price' => 535.00, 'min_stock_level' => 12, 'unit' => 'pcs', 'stock_quantity' => 30],
            ['upc' => '8901030810015', 'sku' => 'ITC-SUNFEAST-DG', 'name' => 'Sunfeast Dark Fantasy Choco Fills 300 g', 'description' => 'Chocolate filled cookies.', 'category' => 'snacks-beverages', 'brand' => 'itc', 'tax' => 'GST 18%', 'mrp' => 120.00, 'selling_price' => 110.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 48],
            ['upc' => '8901030820014', 'sku' => 'ITC-BINGO-MAD', 'name' => 'Bingo Mad Angles Achaari Masti 130 g', 'description' => 'Triangle chips.', 'category' => 'snacks-beverages', 'brand' => 'itc', 'tax' => 'GST 18%', 'mrp' => 40.00, 'selling_price' => 38.00, 'min_stock_level' => 30, 'unit' => 'pcs', 'stock_quantity' => 85],
            ['upc' => '8901030820021', 'sku' => 'ITC-BINGO-TEDHE', 'name' => 'Bingo Tedhe Medhe Masala 90 g', 'description' => 'Masala sticks namkeen.', 'category' => 'snacks-beverages', 'brand' => 'itc', 'tax' => 'GST 18%', 'mrp' => 20.00, 'selling_price' => 18.00, 'min_stock_level' => 35, 'unit' => 'pcs', 'stock_quantity' => 95],
            ['upc' => '8901030830013', 'sku' => 'ITC-YIPPEE-70', 'name' => 'Sunfeast Yippee Magic Masala 70 g', 'description' => 'Instant noodles.', 'category' => 'grocery-staples', 'brand' => 'itc', 'tax' => 'GST 18%', 'mrp' => 14.00, 'selling_price' => 12.00, 'min_stock_level' => 60, 'unit' => 'pcs', 'stock_quantity' => 180],
            ['upc' => '8901030840012', 'sku' => 'ITC-AASH-SALT-1', 'name' => 'Aashirvaad Salt 1 kg', 'description' => 'Iodised salt.', 'category' => 'grocery-staples', 'brand' => 'itc', 'tax' => 'GST 5%', 'mrp' => 28.00, 'selling_price' => 25.00, 'min_stock_level' => 40, 'unit' => 'pcs', 'stock_quantity' => 100],
            ['upc' => '8901030850011', 'sku' => 'ITC-FARM-TOMATO', 'name' => 'ITC Master Chef Tomato Ketchup 1 kg', 'description' => 'Tomato ketchup bottle.', 'category' => 'grocery-staples', 'brand' => 'itc', 'tax' => 'GST 12%', 'mrp' => 145.00, 'selling_price' => 135.00, 'min_stock_level' => 15, 'unit' => 'pcs', 'stock_quantity' => 35],
            ['upc' => '8901030860010', 'sku' => 'ITC-CANDYMAN', 'name' => 'Candyman Eclairs Classic 325 g', 'description' => 'Eclair toffees jar.', 'category' => 'snacks-beverages', 'brand' => 'itc', 'tax' => 'GST 18%', 'mrp' => 100.00, 'selling_price' => 95.00, 'min_stock_level' => 15, 'unit' => 'pcs', 'stock_quantity' => 42],

            // —— Tata Consumer ——
            ['upc' => '8901135120017', 'sku' => 'TATA-SALT-1', 'name' => 'Tata Salt 1 kg', 'description' => 'Iodised vacuum-evaporated salt.', 'category' => 'grocery-staples', 'brand' => 'tata-consumer', 'tax' => 'GST 5%', 'mrp' => 30.00, 'selling_price' => 28.00, 'min_stock_level' => 50, 'unit' => 'pcs', 'stock_quantity' => 150],
            ['upc' => '8901135120024', 'sku' => 'TATA-SALT-5', 'name' => 'Tata Salt 5 kg', 'description' => 'Iodised salt family pack.', 'category' => 'grocery-staples', 'brand' => 'tata-consumer', 'tax' => 'GST 5%', 'mrp' => 140.00, 'selling_price' => 132.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 48],
            ['upc' => '8901135130016', 'sku' => 'TATA-TEA-GOLD-250', 'name' => 'Tata Tea Gold 250 g', 'description' => 'Premium leaf tea.', 'category' => 'snacks-beverages', 'brand' => 'tata-consumer', 'tax' => 'GST 5%', 'mrp' => 165.00, 'selling_price' => 155.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 50],
            ['upc' => '8901135130023', 'sku' => 'TATA-TEA-GOLD-500', 'name' => 'Tata Tea Gold 500 g', 'description' => 'Premium leaf tea.', 'category' => 'snacks-beverages', 'brand' => 'tata-consumer', 'tax' => 'GST 5%', 'mrp' => 320.00, 'selling_price' => 305.00, 'min_stock_level' => 15, 'unit' => 'pcs', 'stock_quantity' => 36],
            ['upc' => '8901135140015', 'sku' => 'TATA-TEA-PREM-250', 'name' => 'Tata Tea Premium 250 g', 'description' => 'Assam leaf tea.', 'category' => 'snacks-beverages', 'brand' => 'tata-consumer', 'tax' => 'GST 5%', 'mrp' => 145.00, 'selling_price' => 138.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 44],
            ['upc' => '8901135150014', 'sku' => 'TATA-SAMPANN-TUR', 'name' => 'Tata Sampann Toor Dal 1 kg', 'description' => 'Unpolished toor dal.', 'category' => 'grocery-staples', 'brand' => 'tata-consumer', 'tax' => 'GST 5%', 'mrp' => 185.00, 'selling_price' => 175.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 50],
            ['upc' => '8901135150021', 'sku' => 'TATA-SAMPANN-CHANA', 'name' => 'Tata Sampann Chana Dal 1 kg', 'description' => 'Unpolished chana dal.', 'category' => 'grocery-staples', 'brand' => 'tata-consumer', 'tax' => 'GST 5%', 'mrp' => 145.00, 'selling_price' => 138.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 48],
            ['upc' => '8901135160013', 'sku' => 'TATA-SAMPANN-BESAN', 'name' => 'Tata Sampann Besan 500 g', 'description' => 'Gram flour.', 'category' => 'grocery-staples', 'brand' => 'tata-consumer', 'tax' => 'GST 5%', 'mrp' => 75.00, 'selling_price' => 70.00, 'min_stock_level' => 25, 'unit' => 'pcs', 'stock_quantity' => 60],
            ['upc' => '8901135170012', 'sku' => 'TATA-HIMALAYAN-1', 'name' => 'Himalayan Sparkling Water 1 L', 'description' => 'Natural mineral sparkling water.', 'category' => 'snacks-beverages', 'brand' => 'tata-consumer', 'tax' => 'GST 18%', 'mrp' => 65.00, 'selling_price' => 60.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 40],

            // —— Extra grocery staples (mixed brands) to cross 50 ——
            ['upc' => '8901262500012', 'sku' => 'AMUL-PANEER-200', 'name' => 'Amul Fresh Paneer 200 g', 'description' => 'Malai paneer block.', 'category' => 'grocery-staples', 'brand' => 'amul', 'tax' => 'GST 5%', 'mrp' => 95.00, 'selling_price' => 90.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 45],
            ['upc' => '8901262500029', 'sku' => 'AMUL-CREAM-250', 'name' => 'Amul Fresh Cream 250 ml', 'description' => 'Dairy cream tetra pack.', 'category' => 'grocery-staples', 'brand' => 'amul', 'tax' => 'GST 12%', 'mrp' => 70.00, 'selling_price' => 66.00, 'min_stock_level' => 20, 'unit' => 'pcs', 'stock_quantity' => 50],
            ['upc' => '8901058070017', 'sku' => 'NEST-MAGGI-ATTA', 'name' => 'Maggi Atta Masala Noodles 72.5 g', 'description' => 'Whole wheat instant noodles.', 'category' => 'grocery-staples', 'brand' => 'nestle', 'tax' => 'GST 18%', 'mrp' => 20.00, 'selling_price' => 18.00, 'min_stock_level' => 40, 'unit' => 'pcs', 'stock_quantity' => 100],
            ['upc' => '8901030870019', 'sku' => 'ITC-AASH-SUJI', 'name' => 'Aashirvaad Select Suji 1 kg', 'description' => 'Rava / sooji.', 'category' => 'grocery-staples', 'brand' => 'itc', 'tax' => 'GST 5%', 'mrp' => 65.00, 'selling_price' => 60.00, 'min_stock_level' => 25, 'unit' => 'pcs', 'stock_quantity' => 55],
            ['upc' => '8901135180011', 'sku' => 'TATA-TEA-GOLD-1K', 'name' => 'Tata Tea Gold 1 kg', 'description' => 'Premium leaf tea value pack.', 'category' => 'snacks-beverages', 'brand' => 'tata-consumer', 'tax' => 'GST 5%', 'mrp' => 610.00, 'selling_price' => 585.00, 'min_stock_level' => 10, 'unit' => 'pcs', 'stock_quantity' => 22],
            ['upc' => '8901063080010', 'sku' => 'BRIT-NUTRICHOICE', 'name' => 'Britannia NutriChoice Digestive 1 kg', 'description' => 'High-fibre digestive biscuits.', 'category' => 'snacks-beverages', 'brand' => 'britannia', 'tax' => 'GST 18%', 'mrp' => 180.00, 'selling_price' => 169.00, 'min_stock_level' => 15, 'unit' => 'pcs', 'stock_quantity' => 35],
            ['upc' => '8901719170018', 'sku' => 'PARLE-FAB', 'name' => 'Parle Fab! Bourbon 150 g', 'description' => 'Chocolate cream sandwich biscuits.', 'category' => 'snacks-beverages', 'brand' => 'parle', 'tax' => 'GST 18%', 'mrp' => 40.00, 'selling_price' => 36.00, 'min_stock_level' => 25, 'unit' => 'pcs', 'stock_quantity' => 70],
        ];
    }

    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');
        $brands = Brand::query()->pluck('id', 'slug');
        $taxClasses = TaxClass::query()->pluck('id', 'name');

        foreach (self::catalog() as $item) {
            $categoryId = $categories[$item['category']] ?? null;
            $brandId = $brands[$item['brand']] ?? null;
            $taxClassId = $taxClasses[$item['tax']] ?? null;

            if (! $categoryId) {
                throw new RuntimeException("Missing category slug [{$item['category']}]. Run CategorySeeder first.");
            }

            if (! $brandId) {
                throw new RuntimeException("Missing brand slug [{$item['brand']}]. Run BrandSeeder first.");
            }

            Product::query()->updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'upc' => $item['upc'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'tax_class_id' => $taxClassId,
                    'mrp' => $item['mrp'],
                    'selling_price' => $item['selling_price'],
                    'min_stock_level' => $item['min_stock_level'],
                    'unit' => $item['unit'],
                    'stock_quantity' => $item['stock_quantity'],
                    'image' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
