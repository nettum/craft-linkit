<?php

namespace fruitstudios\linkit\migrations;

use Craft;
use craft\db\Query;
use craft\db\Migration;

/**
 * m190313_161737_ids_to_uid migration.
 */
class m190313_161737_ids_to_uid extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $linkItFields = (new Query())
            ->select(['id', 'uid', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'fruitstudios\linkit\fields\LinkitField'])
            ->all();
        
        foreach($linkItFields as $field) {
            $needsUpdate = false;
            $settings = \json_decode($field['settings'], true);
            foreach($settings['types'] as $model => $type) {
                if (!isset($type['sources']) || !is_array($type['sources'])) {
                    continue;
                }
                foreach($type['sources'] as $key => $source) {
                    $sourceArr = explode(':', $source, 2);
                    if (count($sourceArr) !== 2) {
                        continue;
                    }
                    // Entry source
                    if ($key === 'fruitstudios\linkit\models\Entry') {
                        $sectionUid = (new Query())
                            ->select('uid')
                            ->from(['{{%sections}}'])
                            ->where(['id' => $sourceArr[1]])
                            ->column();
                        if (!isset($sectionUid[0])) {
                            continue;
                        }
                        $settings['types']['fruitstudios\linkit\models\Entry']['sources'][$key] = 'section:' . $sectionUid[0];
                        $needsUpdate = true;
                    }
                    // Category source
                    if ($key === 'fruitstudios\linkit\models\Category') {
                        $groupUid = (new Query())
                            ->select('uid')
                            ->from(['{{%categorygroups}}'])
                            ->where(['id' => $sourceArr[1]])
                            ->column();
                        if (!isset($groupUid[0])) {
                            continue;
                        }
                        $settings['types']['fruitstudios\linkit\models\Category']['sources'][$key] = 'group:' . $groupUid[0];
                        $needsUpdate = true;
                    }
                    // Asset source
                    if ($key === 'fruitstudios\linkit\models\Asset') {
                        $folderUid = (new Query())
                            ->select('uid')
                            ->from(['{{%volumefolders}}'])
                            ->where(['id' => $sourceArr[1]])
                            ->column();
                        if (!isset($folderUid[0])) {
                            continue;
                        }
                        $settings['types']['fruitstudios\linkit\models\Asset']['sources'][$key] = 'folder:' . $folderUid[0];
                        $needsUpdate = true;
                    }
                    // User souces
                    if ($key === 'fruitstudios\linkit\models\User') {
                        $usergroupUid = (new Query())
                            ->select('uid')
                            ->from(['{{%usergroups}}'])
                            ->where(['id' => $sourceArr[1]])
                            ->column();
                        if (!isset($usergroupUid[0])) {
                            continue;
                        }
                        $settings['types']['fruitstudios\linkit\models\User']['sources'][$key] = 'group:' . $usergroupUid[0];
                        $needsUpdate = true;
                    }
                    // @TODO: product source
                }
            }
            if (!$needsUpdate) {
                continue;
            }
            $this->update('{{%fields}}', [
                'settings' => json_encode($settings)
            ], ['id' => $field['id']], [], false);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190313_161737_ids_to_uid cannot be reverted.\n";
        return false;
    }
}
