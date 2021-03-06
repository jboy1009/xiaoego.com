<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\web\JsExpression;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use common\models\Goods;
use common\models\Category;
use common\models\Store;

$this->title = '商品列表';
?>
<p>
    <?= Html::a('<i class="fa fa-plus"></i> 添加商品', ['goods/add'], ['class' => 'btn btn-primary']) ?>
</p>
<div class="row">
    <div class="col-lg-12">
        <?php Pjax::begin() ?>
        <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-striped table-bordered table-center'],
                'summaryOptions' => ['tag' => 'p', 'class' => 'text-right text-info'],
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['class' => '']
                    ],
                    [
                        'attribute' => 'name',
                        'headerOptions' => ['class' => 'col-md-2'],
                        'filterInputOptions' => ['class' => 'form-control input-sm']
                    ],
                    [
                        'attribute' => 'category_id',
                        'headerOptions' => ['class' => 'col-md-1'],
                        'filter' => Category::getKeyValuePairs(),
                        'filterInputOptions' => ['class' => 'form-control input-sm'],
                        'value' => function ($model, $key, $index, $column) {
                            return $model->category->name;
                        }
                    ],
                    [
                        'attribute' => 'store_id',
                        'headerOptions' => ['class' => 'col-md-2'],
                        'filterInputOptions' => ['class' => 'form-control input-sm'],
                        'value' => function ($model, $key, $index, $column) {
                            return $model->store->school->name . '-' . $model->store->name;
                        },
                        'filter' => Select2::widget([
                            'model' => $searchModel,
                            'initValueText' => ($store = Store::findOne($searchModel->store_id)) ? $store->name : '' ,
                            'attribute' => 'store_id',
                            'size' => Select2::SMALL,
                            'theme' => Select2::THEME_KRAJEE,
                            'options' => ['placeholder' => '搜索店铺名称...'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 3,
                                'ajax' => [
                                    'url' => Url::to(['/store/name-filter']),
                                    'dataType' => 'json',
                                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'templateResult' => new JsExpression('function (store) { return store.text; }'),
                                'templateSelection' => new JsExpression('function (store) { return store.text; }'),
                            ]
                        ]),
                    ],
                    [
                        'attribute' => 'price',
                        'headerOptions' => ['class' => 'col-md-1'],
                        'format' => 'html',
                        'filterInputOptions' => ['class' => 'form-control input-sm', 'title' => '支持运算符'],
                        'value' => function ($model, $key, $index, $column) {
                            return '&yen; ' . $model->price;
                        }
                    ],
                    [
                        'attribute' => 'surplus',
                        'headerOptions' => ['class' => 'col-md-1'],
                        'filterInputOptions' => ['class' => 'form-control input-sm', 'title' => '支持运算符']
                    ],
                    [
                        'attribute' => 'sales',
                        'headerOptions' => ['class' => 'col-md-1'],
                        'filterInputOptions' => ['class' => 'form-control input-sm', 'title' => '支持运算符']
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'filter' => Goods::getStatusList(),
                        'filterInputOptions' => ['class' => 'form-control input-sm'],
                        'headerOptions' => ['class' => 'col-md-1'],
                        'value' => function ($model, $key, $index, $column) {
                            return Html::dropDownList('status', $model->status, Goods::getStatusList(), ['data-id' => $model->id]);
                        }
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:Y-m-d H:i'],
                        'filter' => DatePicker::widget([
                            'model' => $searchModel,
                            'type' => DatePicker::TYPE_COMPONENT_APPEND,
                            'attribute' => 'date',
                            'options' => ['class' => 'input-sm'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd'
                            ]
                        ]),
                        'headerOptions' => ['class' => 'col-md-2']
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'headerOptions' => ['class' => 'col-md-1'],
                        'template' => '{update} {img} {surplus}',
                        'buttons' => [
                            'img' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-picture" aria-hidden="true"></span>', $url, ['title' => '更新图片']);
                            },
                            'surplus' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-equalizer" aria-hidden="true"></span>', $url, ['title' => '库存变化记录']);
                            },
                        ]
                    ]
                ]
        ]) ?>
        <?php Pjax::end() ?>
    </div>
</div>
<?php
$url = Url::to(['/goods/status']);
$js = <<<JS
var handle = function () {
    var id = $(this).attr('data-id');
    var status = $(this).val();
    $.ajax({
        url: '{$url}?id=' + id ,
        type: 'post',
        dataType: 'json',
        data: {status: status},
        success: function () {},
        error: function () {}
    });
};
$('select[name="status"]').change(handle);

$(document).on('pjax:complete', function() {
    $('select[name="status"]').change(handle);
})
JS;

$this->registerJs($js);
?>