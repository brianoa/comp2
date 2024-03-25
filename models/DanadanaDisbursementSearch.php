<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\DanadanaDisbursement;

/**
 * DanadanaDisbursementSearch represents the model behind the search form of `app\models\DanadanaDisbursement`.
 */
class DanadanaDisbursementSearch extends DanadanaDisbursement
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'reference_id', 'reference_name', 'phone_number', 'conversation_id', 'disbursement_type', 'transaction_reference', 'created_at', 'updated_at', 'deleted_at', 'unique_field', 'station_id', 'description', 'descriptio'], 'safe'],
            [['amount'], 'number'],
            [['status'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
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
    public function search($params, $daily = false, $monthly = false, $from = null, $to = null)
    {
        $query = DanadanaDisbursement::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'reference_id', $this->reference_id])
            ->andFilterWhere(['like', 'reference_name', $this->reference_name])
            ->andFilterWhere(['like', 'phone_number', $this->phone_number])
            ->andFilterWhere(['like', 'conversation_id', $this->conversation_id])
            ->andFilterWhere(['like', 'disbursement_type', $this->disbursement_type])
            ->andFilterWhere(['like', 'transaction_reference', $this->transaction_reference]);
        $today     = date( 'Y-m-d' );
        $yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
        if ( $daily ) {
                $query->andWhere( "DATE(created_at)>= DATE('" . $yesterday . "')" );
                $query->andWhere( "DATE(created_at)<= DATE('" . $today . "')" );
        }
        if ( $monthly ) {
                $query->andWhere( "MONTH(created_at)= MONTH(CURDATE())" );
                $query->andWhere( "YEAR(created_at)= YEAR(CURDATE())" );
        }
        if ( $from != null && $to != null ) {
                $query->andWhere( "DATE(created_at)>= DATE('" . $from . "')" );
                $query->andWhere( "DATE(created_at)<= DATE('" . $to . "')" );
        }
        if(isset($_GET['t']) && $_GET['t'] == 'p'){
            $query->andWhere('disbursement_type = "presenter_commission"');
        }
        if(\Yii::$app->myhelper->isStationManager()){
           $query->andWhere(['IN','station_id', \Yii::$app->myhelper->getStations()]); 
        }
        $query->orderBy('created_at DESC');
        return $dataProvider;
    }
}
