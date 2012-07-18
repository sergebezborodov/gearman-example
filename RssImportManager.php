<?php

/**
 * Менеджер импортов RSS фидов
 */
class RssImportManager extends CComponent
{
    /**
     * @var bool флаг, работа без очереди германа
     */
    protected $now;

    /**
     * @var array список id партнеров для обработки
     */
    protected $partnerIds;

    /**
     * Иницилизация менеджера
     *
     * @param array $params
     *  ids  array id партнеров для обработки
     * @param bool  $now
     */
    public function init($params = array(), $now = false)
    {
        $command = Yii::app()->db->createCommand()
            ->select('id')
            ->from('partner');

        if (!empty($params['ids'])) {
            $command->where(array('and', array('in', 'id', $params['ids']), 'is_enabled = 1'));
        } else {
            $command->where('is_enabled = 1');
        }

        $this->partnerIds = $command->queryColumn();

        $this->now = $now;
    }

    /**
     * Запуск процесса
     */
    public function run()
    {
        $worker = new RssImportWorker;

        if ($this->now) {
            foreach ($this->partnerIds as $partnerId) {
                $worker->addTask($partnerId);
            }
        } else {
            foreach ($this->partnerIds as $partnerId) {
                $worker->runTask($partnerId);
            }

            Yii::app()->db->active = false;
            $worker->executeTasks();
            Yii::app()->db->active = true;
        }
    }
}