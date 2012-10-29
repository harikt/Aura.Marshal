<?php
namespace Aura\Marshal;

use Aura\Marshal\Collection\Builder as CollectionBuilder;
use Aura\Marshal\Record\Builder as RecordBuilder;
use Aura\Marshal\Record\GenericCollection;
use Aura\Marshal\Record\GenericRecord;
use Aura\Marshal\Relation\Builder as RelationBuilder;
use Aura\Marshal\Type\Builder as TypeBuilder;
use Aura\Marshal\Type\GenericType;
use Aura\Marshal\Proxy\Builder as ProxyBuilder;

/**
 * Test class for Type.
 * Generated by PHPUnit on 2011-11-21 at 18:02:55.
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GenericType
     */
    protected $type;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $types = include __DIR__ . DIRECTORY_SEPARATOR . 'fixture_types.php';
        $info = $types['posts'];
        
        $this->type = new GenericType;
        $this->type->setIdentityField($info['identity_field']);
        $this->type->setIndexFields($info['index_fields']);
        $this->type->setRecordBuilder(new RecordBuilder(new ProxyBuilder));
        $this->type->setCollectionBuilder(new CollectionBuilder);
    }
    
    protected function loadTypeWithPosts()
    {
        $data = include __DIR__ . DIRECTORY_SEPARATOR . 'fixture_data.php';
        $this->type->load($data['posts']);
        return $data['posts'];
    }
    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testSetAndGetIdentityField()
    {
        $expect = 'foobar';
        $this->type->setIdentityField('foobar');
        $actual = $this->type->getIdentityField();
        $this->assertSame($expect, $actual);
    }
    
    public function testSetAndGetIndexFields()
    {
        $expect = ['foobar', 'bazdib'];
        $this->type->setIndexFields($expect);
        $actual = $this->type->getIndexFields();
        $this->assertSame($expect, $actual);
        
    }
    public function testSetAndGetRecordBuilder()
    {
        $builder = new RecordBuilder;
        $this->type->setRecordBuilder($builder);
        $actual = $this->type->getRecordBuilder();
        $this->assertSame($builder, $actual);
    }
    
    public function testSetAndGetCollectionBuilder()
    {
        $builder = new CollectionBuilder;
        $this->type->setCollectionBuilder($builder);
        $actual = $this->type->getCollectionBuilder();
        $this->assertSame($builder, $actual);
    }
    
    public function testLoadAndGetStorage()
    {
        $data = $this->loadTypeWithPosts();
        $expect = count($data);
        $actual = count($this->type);
        $this->assertSame($expect, $actual);
        
        // try loading again to make sure we don't double-load.
        // $expect stays as the original count value.
        $this->loadTypeWithPosts();
        $actual = count($this->type);
        $this->assertSame($expect, $actual);
    }
    
    public function testGetIdentityValues()
    {
        $data = $this->loadTypeWithPosts();
        $expect = [1, 2, 3, 4, 5];
        $actual = $this->type->getIdentityValues();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetFieldValues()
    {
        $data = $this->loadTypeWithPosts();
        $expect = [1 => '1', 2 => '1', 3 => '1', 4 => '2', 5 => '2'];
        $actual = $this->type->getFieldValues('author_id');
        $this->assertSame($expect, $actual);
    }
    
    public function testGetRecord()
    {
        $data = $this->loadTypeWithPosts();
        $expect = (object) $data[2];
        $actual = $this->type->getRecord(3);
        
        $this->assertSame($expect->id, $actual->id);
        $this->assertSame($expect->author_id, $actual->author_id);
        $this->assertSame($expect->body, $actual->body);
        
        // get it again for complete code coverage
        $again = $this->type->getRecord(3);
        $this->assertSame($actual, $again);
    }
    
    public function testGetRecord_none()
    {
        $data = $this->loadTypeWithPosts();
        $actual = $this->type->getRecord(999);
        $this->assertNull($actual);
    }
    
    public function testGetRecordByField_identity()
    {
        $data = $this->loadTypeWithPosts();
        $expect = (object) $data[3];
        $actual = $this->type->getRecordByField('id', 4);
        
        $this->assertSame($expect->id, $actual->id);
        $this->assertSame($expect->author_id, $actual->author_id);
        $this->assertSame($expect->body, $actual->body);
    }
    
    public function testGetRecordByField_index()
    {
        $data = $this->loadTypeWithPosts();
        $expect = (object) $data[3];
        $actual = $this->type->getRecordByField('author_id', 2);
        
        $this->assertSame($expect->id, $actual->id);
        $this->assertSame($expect->author_id, $actual->author_id);
        $this->assertSame($expect->body, $actual->body);
    }
    
    public function testGetRecordByField_indexNone()
    {
        $data = $this->loadTypeWithPosts();
        $actual = $this->type->getRecordByField('author_id', 'no such value');
        $this->assertNull($actual);
    }
    
    public function testGetRecordByField_loop()
    {
        $data = $this->loadTypeWithPosts();
        $expect = (object) $data[3];
        $actual = $this->type->getRecordByField('fake_field', '88');
        
        $this->assertSame($expect->id, $actual->id);
        $this->assertSame($expect->author_id, $actual->author_id);
        $this->assertSame($expect->body, $actual->body);
        $this->assertSame($expect->fake_field, $actual->fake_field);
    }
    
    public function testGetRecordByField_loopNone()
    {
        $data = $this->loadTypeWithPosts();
        $actual = $this->type->getRecordByField('fake_field', 'no such value');
        $this->assertNull($actual);
    }
    
    public function getCollection()
    {
        $data = $this->loadTypeWithPosts();
        $collection = $this->type->getCollection([1, 2, 3]);
        $expect = [
            (object) $data[0],
            (object) $data[1],
            (object) $data[2],
        ];
        
        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
        }
    }
    
    public function testGetCollectionByField()
    {
        $data = $this->loadTypeWithPosts();
        $collection = $this->type->getCollectionByField('fake_field', 88);
        $expect = [
            (object) $data[3],
            (object) $data[4],
        ];
        
        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }
    
    public function testGetCollectionByField_many()
    {
        $data = $this->loadTypeWithPosts();
        $collection = $this->type->getCollectionByField('fake_field', [88, 69]);
        $expect = [
            (object) $data[0],
            (object) $data[1],
            (object) $data[2],
            (object) $data[3],
            (object) $data[4],
        ];
        
        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }
    
    public function testGetCollectionByField_identity()
    {
        $data = $this->loadTypeWithPosts();
        $collection = $this->type->getCollectionByField('id', [4, 5]);
        $expect = [
            (object) $data[3],
            (object) $data[4],
        ];
        
        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }
    
    public function getCollectionByField_index()
    {
        $data = $this->loadTypeWithPosts();
        $collection = $this->type->getCollectionByField('author_id', [2, 1]);
        $expect = [
            (object) $data[3],
            (object) $data[4],
            (object) $data[0],
            (object) $data[1],
            (object) $data[2],
        ];
        
        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }
    
    public function testAddAndGetRelation()
    {
        $type_builder = new TypeBuilder;
        $relation_builder = new RelationBuilder;
        $types = include __DIR__ . DIRECTORY_SEPARATOR . 'fixture_types.php';
        $manager = new Manager($type_builder, $relation_builder, $types);
        
        $name = 'meta';
        $info = $types['posts']['relation_names'][$name];
        
        $relation = $relation_builder->newInstance('posts', $name, $info, $manager);
        $this->type->setRelation($name, $relation);
        
        $actual = $this->type->getRelation('meta');
        $this->assertSame($relation, $actual);
        
        // try again again, should fail
        $this->setExpectedException('Aura\Marshal\Exception');
        $this->type->setRelation($name, $relation);
    }
    
    public function testTypeBuilder_noIdentityField()
    {
        $type_builder = new TypeBuilder;
        $this->setExpectedException('Aura\Marshal\Exception');
        $type = $type_builder->newInstance([]);
    }
    
    public function testNewRecord()
    {
        $this->loadTypeWithPosts();
        $before = count($this->type);
        
        // do we actually get a new record back?
        $record = $this->type->newRecord();
        $this->assertInstanceOf('Aura\Marshal\Record\GenericRecord', $record);
        
        // has it been added to the identity map?
        $expect = $before + 1;
        $actual = count($this->type);
        $this->assertSame($expect, $actual);
    }
    
    public function testGetChangedRecords()
    {
        $data = $this->loadTypeWithPosts();
        
        // change record id 1 and 3
        $record_1 = $this->type->getRecord(1);
        $record_1->fake_field = 'changed';
        $record_3 = $this->type->getRecord(3);
        $record_3->fake_field = 'changed';
        
        // get record 2 but don't change it
        $record_2 = $this->type->getRecord(2);
        $fake_field = $record_2->fake_field;
        $record_2->fake_field = $fake_field;
        
        // now check for changes
        $expect = [
            $record_1->id => $record_1,
            $record_3->id => $record_3,
        ];
        
        $actual = $this->type->getChangedRecords();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetNewRecords()
    {
        $data = $this->loadTypeWithPosts();
        $expect = [
            $this->type->newRecord(['fake_field' => 101]),
            $this->type->newRecord(['fake_field' => 102]),
            $this->type->newRecord(['fake_field' => 105]),
        ];
        $actual = $this->type->getNewRecords();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetInitialData_noRecord()
    {
        $record = new \StdClass;
        $this->assertNull($this->type->getInitialData($record));
    }
    
    public function testGetChangedFields_numeric()
    {
        $this->loadTypeWithPosts();
        $record = $this->type->getRecord(1);
        
        // change from string '69' to int 69;
        // it should not be marked as a change
        $record->fake_field = 69;
        $expect = [];
        $actual = $this->type->getChangedFields($record);
        $this->assertSame($expect, $actual);
        
        $record->fake_field = 4.56;
        $expect = ['fake_field' => 4.56];
        $actual = $this->type->getChangedFields($record);
        $this->assertSame($expect, $actual);
    }
    
    public function testGetChangedFields_toNull()
    {
        $this->loadTypeWithPosts();
        $record = $this->type->getRecord(1);
        
        $record->fake_field = null;
        $expect = ['fake_field' => null];
        $actual = $this->type->getChangedFields($record);
        $this->assertSame($expect, $actual);
    }
    
    public function testGetChangedFields_fromNull()
    {
        $this->loadTypeWithPosts();
        $record = $this->type->getRecord(1);
        
        $record->null_field = 0;
        $expect = ['null_field' => 0];
        $actual = $this->type->getChangedFields($record);
        $this->assertSame($expect, $actual);
    }
    
    public function testGetChangedFields_other()
    {
        $this->loadTypeWithPosts();
        $record = $this->type->getRecord(1);
        
        $record->fake_field = 'changed';
        $expect = ['fake_field' => 'changed'];
        $actual = $this->type->getChangedFields($record);
        $this->assertSame($expect, $actual);
    }
    
    public function testLoadRecord()
    {
        $initial_data = [
            'id'  => 88,
            'author_id' => 69,
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => 'gir',
        ];
        
        $record = $this->type->loadRecord($initial_data);
        foreach ($initial_data as $field => $value) {
            $this->assertSame($value, $record->$field);
        }
    }
    
    public function testLoadCollection()
    {
        $data = include __DIR__ . DIRECTORY_SEPARATOR . 'fixture_data.php';
        $collection = $this->type->loadCollection($data['posts']);
        $this->assertInstanceOf(
            'Aura\Marshal\Collection\GenericCollection',
            $collection
        );
        
    }
}
