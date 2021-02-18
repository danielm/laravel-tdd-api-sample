<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;


use Tests\TestCase;


use App\Models\Post;
use App\Models\User;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_store()
    {
        //$this->withoutExceptionHandling();

        $user = User::factory()->create();

        //$this->withoutMiddleware();

        $response = $this->actingAs($user, 'api')->json('POST', route('posts.store'), [
            'title' => 'Testing new Post',
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => 'Testing new Post'])
            ->assertStatus(201);
        
        $this->assertDatabaseHas('posts', ['title' => 'Testing new Post']);
    }

    public function test_title_validation()
    {
        //$this->withoutExceptionHandling();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('POST', route('posts.store'), [
            'title' => '',
        ]);

        //var_dump($response);die;

        $response->assertJsonValidationErrors('title')
            ->assertStatus(422);
    }

    public function test_show()
    {
        //$this->withoutExceptionHandling();

        $post = Post::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET', route('posts.show', $post));

        $response->assertJsonStructure(['id','title','created_at', 'updated_at'])
            ->assertJson(['title'=> $post->title])
            ->assertStatus(200);
    }

    public function test_404_show()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts/1000');

        $response->assertStatus(404);
    }

    public function test_update()
    {
        //$this->withoutExceptionHandling();

        $post = Post::factory()->create();
        $user = User::factory()->create();

        $update_title_screen = 'My updated title';

        $response = $this->actingAs($user, 'api')->json('PUT', route('posts.update', $post), [
            'title' => $update_title_screen,
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => $update_title_screen])
            ->assertStatus(200);
        
        $this->assertDatabaseHas('posts', ['title' => $update_title_screen]);
    }

    public function test_destroy()
    {
        //$this->withoutExceptionHandling();

        $post = Post::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', route('posts.destroy', $post));

        $response->assertSee(null)
            ->assertStatus(204);
        
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_index()
    {
        //$this->withoutExceptionHandling();

        Post::factory()->count(5)->create();
        
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET', route('posts.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id','title','created_at','updated_at']
            ]
        ])->assertStatus(200);
    }

    public function test_guest()
    {
        //$this->withoutExceptionHandling();
        $post = Post::factory()->create();

        $this->json('GET',    route('posts.index'))->assertStatus(401);
        $this->json('POST',   route('posts.store'))->assertStatus(401);
        $this->json('GET',    route('posts.show', $post))->assertStatus(401);
        $this->json('PUT',    route('posts.update', $post))->assertStatus(401);
        $this->json('DELETE', route('posts.destroy', $post))->assertStatus(401);
    }
}
