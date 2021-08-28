<?php

namespace backend\controllers;

use Yii;
use common\models\Book;
use common\models\BookSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * BookController implements the CRUD actions for Book model.
 */
class BookController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Book models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BookSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        $dataProvider->query->joinWith(['topic','language','user',]);
        
                
        $dataProvider->sort->attributes['topic.name'] = [
            'asc' => ['topic.name' => SORT_ASC],
            'desc' => ['topic.name' => SORT_DESC],
        ];
        
                
        $dataProvider->sort->attributes['language.name'] = [
            'asc' => ['language.name' => SORT_ASC],
            'desc' => ['language.name' => SORT_DESC],
        ];
        
                
        $dataProvider->sort->attributes['user.name'] = [
            'asc' => ['user.name' => SORT_ASC],
            'desc' => ['user.name' => SORT_DESC],
        ];
        
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Book model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Book model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Book();

           
        $model->imageFileName_Fl = UploadedFile::getInstance($model, 'imageFileName_Fl');
            if ($model->imageFileName_Fl && $model->validate()) { 
                $s2=$model->id . '_' . $model->imageFileName_Fl->baseName . '.' . $model->imageFileName_Fl->extension;               
                $model->imageFileName_Fl->saveAs(Yii::getAlias('@frontend').'/web/uploads/' . $s2);
                $model->imageFileName=$s2;
            }
                
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Book model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

           
        $model->imageFileName_Fl = UploadedFile::getInstance($model, 'imageFileName_Fl');
            if ($model->imageFileName_Fl && $model->validate()) { 
                $s2=$model->id . '_' . $model->imageFileName_Fl->baseName . '.' . $model->imageFileName_Fl->extension;               
                $model->imageFileName_Fl->saveAs(Yii::getAlias('@frontend').'/web/uploads/' . $s2);
                $model->imageFileName=$s2;
            }
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Book model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Book model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Book the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Book::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
