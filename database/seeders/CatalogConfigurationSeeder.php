<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */

            $technology = $this->category('Technology & Electronics');

            $computers = $this->category(
                'Computers',
                $technology
            );

            $laptop = $this->category(
                'Laptop',
                $computers
            );


            $mobile = $this->category(
                'Mobile',
                $technology
            );

            $smartphone = $this->category(
                'Smartphone',
                $mobile
            );


            $furniture = $this->category(
                'School Furniture'
            );

            $chair = $this->category(
                'Student Chair',
                $furniture
            );


            $stationery = $this->category(
                'Stationery'
            );

            $notebook = $this->category(
                'Notebook',
                $stationery
            );



            /*
            |--------------------------------------------------------------------------
            | Attribute Groups
            |--------------------------------------------------------------------------
            */

            $general = $this->group(
                'General Information'
            );

            $processor = $this->group(
                'Processor'
            );

            $memory = $this->group(
                'Memory'
            );

            $storage = $this->group(
                'Storage'
            );

            $display = $this->group(
                'Display'
            );

            $camera = $this->group(
                'Camera'
            );

            $physical = $this->group(
                'Physical Specification'
            );

            $warranty = $this->group(
                'Warranty'
            );

            $furnitureGroup = $this->group(
                'Furniture Specification'
            );

            $stationeryGroup = $this->group(
                'Stationery Specification'
            );



            /*
            |--------------------------------------------------------------------------
            | Laptop Attributes
            |--------------------------------------------------------------------------
            */

            $brand = $this->attribute(
                $general,
                'Brand',
                'select',
                [
                    'Lenovo',
                    'Dell',
                    'HP',
                    'Apple',
                    'Asus'
                ]
            );


            $model = $this->attribute(
                $general,
                'Model',
                'text'
            );


            $processorBrand = $this->attribute(
                $processor,
                'Processor Brand',
                'select',
                [
                    'Intel',
                    'AMD',
                    'Apple'
                ]
            );


            $processorModel = $this->attribute(
                $processor,
                'Processor Model',
                'text'
            );


            $ram = $this->attribute(
                $memory,
                'RAM Size',
                'select',
                [
                    '4GB',
                    '8GB',
                    '16GB',
                    '32GB'
                ]
            );


            $ramType = $this->attribute(
                $memory,
                'RAM Type',
                'select',
                [
                    'DDR4',
                    'DDR5',
                    'LPDDR5'
                ]
            );


            $storageType = $this->attribute(
                $storage,
                'Storage Type',
                'select',
                [
                    'HDD',
                    'SSD',
                    'NVMe SSD'
                ]
            );


            $storageCapacity = $this->attribute(
                $storage,
                'Storage Capacity',
                'select',
                [
                    '256GB',
                    '512GB',
                    '1TB'
                ]
            );


            $screen = $this->attribute(
                $display,
                'Screen Size',
                'select',
                [
                    '13 inch',
                    '14 inch',
                    '15.6 inch',
                    '16 inch'
                ]
            );


            $resolution = $this->attribute(
                $display,
                'Resolution',
                'select',
                [
                    'Full HD',
                    'WUXGA',
                    'QHD',
                    '4K'
                ]
            );


            $color = $this->attribute(
                $physical,
                'Color',
                'select',
                [
                    'Black',
                    'Silver',
                    'Grey'
                ]
            );


            $weight = $this->attribute(
                $physical,
                'Weight',
                'text'
            );


            $warrantyPeriod = $this->attribute(
                $warranty,
                'Warranty Period',
                'select',
                [
                    '1 Year',
                    '2 Years',
                    '3 Years'
                ]
            );



            /*
            |--------------------------------------------------------------------------
            | Smartphone Attributes
            |--------------------------------------------------------------------------
            */

            $mobileBrand = $brand;

            $this->assign(
                $smartphone,
                [
                    $mobileBrand,
                    $model,
                    $storageCapacity,
                    $ram,
                    $color,
                    $warrantyPeriod
                ]
            );



            /*
            |--------------------------------------------------------------------------
            | Furniture Attributes
            |--------------------------------------------------------------------------
            */

            $material = $this->attribute(
                $furnitureGroup,
                'Material',
                'select',
                [
                    'Wood',
                    'Plastic',
                    'Metal'
                ]
            );


            $chairColor = $this->attribute(
                $furnitureGroup,
                'Chair Color',
                'select',
                [
                    'Blue',
                    'Black',
                    'Green'
                ]
            );


            $capacity = $this->attribute(
                $furnitureGroup,
                'Weight Capacity',
                'number'
            );


            /*
            |--------------------------------------------------------------------------
            | Notebook Attributes
            |--------------------------------------------------------------------------
            */

            $paperSize = $this->attribute(
                $stationeryGroup,
                'Paper Size',
                'select',
                [
                    'A4',
                    'A5',
                    'B5'
                ]
            );


            $pages = $this->attribute(
                $stationeryGroup,
                'Page Count',
                'select',
                [
                    '80 Pages',
                    '120 Pages',
                    '200 Pages'
                ]
            );



            /*
            |--------------------------------------------------------------------------
            | Category Attribute Assignment
            |--------------------------------------------------------------------------
            */

            $this->assign(
                $laptop,
                [
                    $brand,
                    $model,
                    $processorBrand,
                    $processorModel,
                    $ram,
                    $ramType,
                    $storageType,
                    $storageCapacity,
                    $screen,
                    $resolution,
                    $color,
                    $weight,
                    $warrantyPeriod
                ]
            );


            $this->assign(
                $smartphone,
                [
                    $brand,
                    $model,
                    $ram,
                    $storageCapacity,
                    $color,
                    $warrantyPeriod
                ]
            );


            $this->assign(
                $chair,
                [
                    $material,
                    $chairColor,
                    $capacity
                ]
            );


            $this->assign(
                $notebook,
                [
                    $paperSize,
                    $pages
                ]
            );

        });
    }



    private function category($name, $parentId = null)
    {
        $slug = Str::slug($name);

        $exists = DB::table('categories')
            ->where('slug', $slug)
            ->first();

        if ($exists) {
            return $exists->id;
        }

        return DB::table('categories')->insertGetId([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'type' => 'product',
            'approval_status' => 'approved',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }



    private function group($name)
    {
        $slug = Str::slug($name);

        $exists = DB::table('attribute_groups')
            ->where('slug',$slug)
            ->first();

        if ($exists) {
            return $exists->id;
        }

        return DB::table('attribute_groups')->insertGetId([
            'name'=>$name,
            'slug'=>$slug,
            'description'=>$name,
            'is_active'=>1,
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);
    }



    private function attribute(
        $groupId,
        $name,
        $type,
        $values=[]
    )
    {
        $slug = Str::slug($name);


        $exists = DB::table('attributes')
            ->where('slug',$slug)
            ->first();


        if($exists){

            $id=$exists->id;

        }else{

            $id = DB::table('attributes')->insertGetId([
                'attribute_group_id'=>$groupId,
                'name'=>$name,
                'slug'=>$slug,
                'input_type'=>$type,
                'is_filterable'=>1,
                'is_variant'=>0,
                'is_required'=>1,
                'is_active'=>1,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);

        }


        foreach($values as $value){

            DB::table('attribute_values')
                ->insertOrIgnore([
                    'attribute_id'=>$id,
                    'value'=>$value,
                    'slug'=>Str::slug($value),
                    'sort_order'=>0,
                    'is_active'=>1,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);

        }


        return $id;
    }



    private function assign($categoryId,$attributes)
    {
        foreach($attributes as $attributeId){

            DB::table('category_attribute')
                ->insertOrIgnore([
                    'category_id'=>$categoryId,
                    'attribute_id'=>$attributeId,
                    'is_required'=>1,
                    'is_filterable'=>1,
                    'is_variant'=>0,
                    'sort_order'=>0,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
        }
    }
}
