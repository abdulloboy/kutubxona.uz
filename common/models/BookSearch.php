<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Book;

/**
 * BookSearch represents the model behind the search form about `common\models\Book`.
 */
class BookSearch extends Book
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'language_id', 'topic_id', 'user_id'], 'integer'],
            [['name', 'author', 'imageFileName', 'url', 'description'], 'safe'],
            
            [['topic.name','language.name','user.name',],'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Book::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        $query->joinWith('topic')                  
        ->joinWith('language')                  
        ->joinWith('user')                  
        ;

        $query->andFilterWhere([
            'id' => $this->id,
            'language_id' => $this->language_id,
            'topic_id' => $this->topic_id,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'author', $this->author])
            ->andFilterWhere(['like', 'imageFileName', $this->imageFileName])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'description', $this->description]);
        
        $query->andFilterWhere(['like', 'topic.name', $this->getAttribute('topic.name')])
        ->andFilterWhere(['like', 'language.name', $this->getAttribute('language.name')])
        ->andFilterWhere(['like', 'user.name', $this->getAttribute('user.name')])
        ;

        return $dataProvider;
    }
}
