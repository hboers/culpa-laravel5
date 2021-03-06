<?php

namespace Culpa;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Container\Container;

class FullyBlameableTest extends \CulpaTest
{
    private $model;

    public function setUp()
    {
        if (!version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return $this->markTestSkipped('This test uses a model that uses traits.');
        }

        require_once 'Models/FullyBlameableModel.php';

        parent::setUp();
        $this->model = new FullyBlameableModel;
    }

    public function testBlameables()
    {
        $this->assertTrue($this->model->isBlameable('created'), "Created should be blameable");
        $this->assertTrue($this->model->isBlameable('updated'), "Updated should be blameable");
        $this->assertTrue($this->model->isBlameable('deleted'), "Deleted should be blameable");
    }

    public function testCreate()
    {
        $this->model->title = "Hello, world!";
        $this->assertTrue($this->model->save());

        $this->model = FullyBlameableModel::find($this->model->id);

        // Check datetimes are being set properly for sanity's sake
        $this->assertNotNull($this->model->created_at);
        $this->assertEquals($this->model->created_at, $this->model->updated_at);
        $this->assertNull($this->model->deleted_at);

        // Check id references are set
        $this->assertEquals(1, $this->model->created_by_id);
        $this->assertEquals(1, $this->model->updated_by_id);
        $this->assertNull(null, $this->model->deleted_by_id);

        $this->assertEquals(Auth::user()->id, $this->model->created_by->id);
    }

    public function testUpdate()
    {
        $this->model->title = "Hello, world!";
        $this->assertTrue($this->model->save());

        // Make sure updated_at > created_at by at least 1 second
        usleep(1.5 * 1000000); // 1.5 seconds

        $this->model = FullyBlameableModel::find(1);
        $this->model->title = "Test Post, please ignore";
        $this->assertTrue($this->model->save());

        // Check datetimes are being set properly for sanity's sake
        $this->assertNotNull($this->model->created_at);
        $this->assertGreaterThan($this->model->created_at, $this->model->updated_at);
        $this->assertNull($this->model->deleted_at);

        $this->assertEquals(1, $this->model->created_by_id);
        $this->assertEquals(1, $this->model->updated_by_id);
        $this->assertEquals(null, $this->model->deleted_by_id);

        $this->assertEquals(Auth::user()->id, $this->model->updated_by->id);
    }

    public function testDelete()
    {
        $this->model->title = "Hello, world!";
        $this->assertTrue($this->model->save());

        $this->model = FullyBlameableModel::find(1);
        $this->assertTrue($this->model->delete());

        // Reload the model
        $this->model = FullyBlameableModel::withTrashed()->find(1);

        // Check datetimes are being set properly for sanity's sake
        $this->assertNotNull($this->model->created_at);
        $this->assertGreaterThan($this->model->created_at, $this->model->updated_at);
        $this->assertNotNull($this->model->deleted_at);

        $this->assertEquals(1, $this->model->created_by_id);
        $this->assertEquals(1, $this->model->updated_by_id);
        $this->assertEquals(1, $this->model->deleted_by_id);

        $this->assertEquals(Auth::user()->id, $this->model->deleted_by->id);
    }
}
