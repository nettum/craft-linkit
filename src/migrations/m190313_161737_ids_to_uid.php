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
            echo "\n    > Migrating LinkitField sources for field #{$field['id']} ...\n";
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
                    if ($model === 'fruitstudios\linkit\models\Entry') {
                        $sectionUid = (new Query())
                            ->select('uid')
                            ->from(['{{%sections}}'])
                            ->where(['id' => $sourceArr[1]])
                            ->column();
                        if (!isset($sectionUid[0])) {
                            continue;
                        }
                        $settings['types']['fruitstudios\linkit\models\Entry']['sources'][$key] = 'section:' . $sectionUid[0];
                        echo "    > Updating Entry source to use uid {$sectionUid[0]} ...\n";
                        $needsUpdate = true;
                    }
                    // Category source
                    if ($model === 'fruitstudios\linkit\models\Category') {
                        $groupUid = (new Query())
                            ->select('uid')
                            ->from(['{{%categorygroups}}'])
                            ->where(['id' => $sourceArr[1]])
                            ->column();
                        if (!isset($groupUid[0])) {
                            continue;
                        }
                        $settings['types']['fruitstudios\linkit\models\Category']['sources'][$key] = 'group:' . $groupUid[0];
                        echo "    > Updating Category source to use uid {$groupUid[0]} ...\n";
                        $needsUpdate = true;
                    }
                    // Asset source
                    if ($model === 'fruitstudios\linkit\models\Asset') {
                        $folderUid = (new Query())
                            ->select('uid')
                            ->from(['{{%volumefolders}}'])
                            ->where(['id' => $sourceArr[1]])
                            ->column();
                        if (!isset($folderUid[0])) {
                            continue;
                        }
                        $settings['types']['fruitstudios\linkit\models\Asset']['sources'][$key] = 'folder:' . $folderUid[0];
                        echo "    > Updating Asset source to use uid {$folderUid[0]} ...\n";
                        $needsUpdate = true;
                    }
                    // User souces
                    if ($model === 'fruitstudios\linkit\models\User') {
                        $usergroupUid = (new Query())
                            ->select('uid')
                            ->from(['{{%usergroups}}'])
                            ->where(['id' => $sourceArr[1]])
                            ->column();
                        if (!isset($usergroupUid[0])) {
                            continue;
                        }
                        $settings['types']['fruitstudios\linkit\models\User']['sources'][$key] = 'group:' . $usergroupUid[0];
                        echo "    > Updating User source to use uid {$usergroupUid[0]} ...\n";
                        $needsUpdate = true;
                    }
                    // @TODO: product source
                }
            }
            if (!$needsUpdate) {
                echo "    > No migration needed for LinkitField #{$field['id']} ...\n";
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
