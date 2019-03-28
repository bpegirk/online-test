<?php


use Phinx\Migration\AbstractMigration;

class CreateDb extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('users', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true]);
        $table->addColumn('sid', 'string', ['limit' => 40]);
        $table->addColumn('name', 'string', ['limit' => 40, 'null' => true]);
        $table->addColumn('mail', 'string', ['limit' => 40, 'null' => true]);
        $table->addColumn('ip', 'string', ['limit' => 20, 'null' => true]);
        $table->addColumn('done', 'integer', ['signed' => false, 'default' => 0]);
        $table->addColumn('last_active', 'timestamp');
        $table->create();

        $table = $this->table('questions', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true]);
        $table->addColumn('name', 'text');
        $table->addColumn('order', 'integer', ['signed' => false, 'default' => 0]);
        $table->create();

        $table = $this->table('answers', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true]);
        $table->addColumn('question_id', 'integer', ['signed' => false]);
        $table->addColumn('name', 'text');
        $table->addColumn('price', 'integer', ['signed' => false]);
        $table->addColumn('order', 'integer', ['signed' => false, 'default' => 0]);
        $table->addIndex('question_id');
        $table->addForeignKey('question_id', 'questions', 'id', ['update' => 'CASCADE', 'delete' => 'CASCADE']);
        $table->create();

        $table = $this->table('user_answers', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true]);
        $table->addColumn('question_id', 'integer', ['signed' => false]);
        $table->addColumn('answer_id', 'integer', ['signed' => false]);
        $table->addColumn('user_id', 'integer', ['signed' => false]);
        $table->addColumn('order', 'integer', ['signed' => false, 'default' => 0]);
        $table->addColumn('create_time', 'timestamp');
        $table->addIndex('question_id');
        $table->addIndex('answer_id');
        $table->addIndex('user_id');
        $table->addForeignKey('question_id', 'questions', 'id', ['update' => 'CASCADE', 'delete' => 'CASCADE']);
        $table->addForeignKey('answer_id', 'answers', 'id', ['update' => 'CASCADE', 'delete' => 'CASCADE']);
        $table->addForeignKey('user_id', 'users', 'id', ['update' => 'CASCADE', 'delete' => 'CASCADE']);
        $table->create();

        $table = $this->table('results', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true]);
        $table->addColumn('price', 'integer', ['signed' => false]);
        $table->addColumn('name', 'string', ['limit' => 40, 'null' => true]);
        $table->addColumn('info', 'text');
        $table->create();
    }
}
