<?php

/**
 * Обработчик импорта RSS
 */
class RssImportWorker extends CComponent
{
    /**
     * Постановка задачи в очередь
     *
     * @param int $id партнер
     * @return bool
     */
    public function addTask($id)
    {
        // компонент германа, обертка над GearmanClient
        $gearman = Yii::app()->gearman;
        $params = array('id' => $id);
        return $gearman->client()->addTask('importSite', serialize($params));
    }

    /**
     * Запуск добавленных задач в германе
     */
    public function executeTasks()
    {
        Yii::app()->gearman->client()->runTasks();
    }

    /**
     * Неопредственно исполнение задачи
     *
     * @param int $id
     * @return bool
     */
    public function runTask($id)
    {
        // непосредственное исполнение задачи
        // проверка наличия id
        // проверка наличия уже запущенного процесса для данного id
        return RssImportService::import($id);
    }
}