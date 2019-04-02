<?php

namespace App\Admin\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Request;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ProductSku;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class ProductSkusController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('商品Sku管理')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {

        $sku = ProductSku::find($id);
        if (!$sku) {
            throw new InvalidRequestException('未找到该商品');
        }

        $attributes = $sku->productAttribute;
        return Admin::content(function (Content $content) use ($sku,$attributes) {
            $content->header('修改商品库存');
            $products = $this->getProduct();
            $content->body(view('admin.product_sku.create_and_edit', compact('products', 'sku', 'attributes')));
        });

    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {

        return Admin::content(function (Content $content) {
            $content->header('添加商品库存');
            $products = $this->getProduct();
            $sku = new ProductSku();

            $attributes = [];
            $content->body(view('admin.product_sku.create_and_edit', compact('products', 'sku', 'attributes')));
        });

    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProductSku);

        $grid->id('Id');
        $grid->product_id('所属商品')->display(function($product_id) {
            return Product::find($product_id) ? Product::find($product_id)->title : '';
        });
        $grid->description('描述');
        $grid->price('价格');
        $grid->stock('库存');


        //display()方法接收的匿名函数绑定了当前行的数据对象，可以在里面调用当前行的其它字段数据
        $grid->column('商品属性')->display(function (){
            $attributes = $this->productAttribute;
            $data = [];
            foreach ($attributes as $attribute){
                $data[] = $attribute->pivot->val;
            }
            return implode(',',$data);
        });

        $grid->actions(function ($actions) {
            // 不在每一行后面展示查看按钮
            $actions->disableView();
        });

        $grid->filter(function ($filter){
            $filter->like('product.title', '所属商品');
        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ProductSku);

        $form->text('title', 'Title');
        $form->text('description', 'Description');
        $form->decimal('price', 'Price');
        $form->number('stock', 'Stock');
        $form->text('attribute', 'Attribute');
        $form->number('product_id', 'Product id');

        return $form;
    }

    //获取商品列表
    public function getProduct()
    {
        return Product::select(DB::raw('id, title as text'))->get();
    }

    //获取商品属性API
    public function getAttributes($id)
    {
        return ProductAttributes::where([
            ['product_id', '=', $id]
        ])->get();
    }

    public function store(Request $request)
    {
        //创建
        $this->skuSave();
        return [];
    }

    public function update(Request $request)
    {
        //更新
        $this->skuSave();
        return [];
    }

    public function skuSave()
    {
        DB::transaction(function (){

            //判断更新或创建
            if (!($sku_obj = ProductSku::find(request()->input('id')))){
                $sku_obj = new ProductSku();
            }
            //批量赋值
            $sku_obj->fill(request()->all());

            $sku_obj->description = \request()->input('description') ?? '';
            $sku_obj->price       = \request()->input('price');
            $sku_obj->stock       = \request()->input('stock');
            $sku_obj->save();

            //添加商品属性和商品sku库存关联
            $attr_arr = json_decode(request()->input('attributes'), true);
            if (!empty ($attr_arr)){
                foreach ($attr_arr as $arr){
                    //查看商品属性值之前是否存在 更新||创建
                    $attribute = $sku_obj->productAttribute()->where('attribute_id',$arr['id'])->first();
                    if($attribute){
                        $attribute->pivot->update(['val'=>$arr['value']]);
                    }else{
                        $sku_obj->productAttribute()->attach($arr['id'],['val'=>$arr['value']]);
                    }

                }
            }

        });
    }

}
