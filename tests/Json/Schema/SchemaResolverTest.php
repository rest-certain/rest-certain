<?php

declare(strict_types=1);

namespace RestCertain\Test\Json\Schema;

use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\TestCase;
use RestCertain\RestCertain;
use RestCertain\Test\MockWebServer;

use function RestCertain\Hamcrest\assertThat;
use function RestCertain\Json\Schema\matchesJsonSchemaFromUri;
use function file_get_contents;
use function str_replace;

class SchemaResolverTest extends TestCase
{
    use MockWebServer;

    #[BackupStaticProperties(true)]
    public function testWhenRemoteSchemaContainsRefsToOtherRemoteSchemas(): void
    {
        RestCertain::$config?->jsonSchemaConfig->resolver->registerProtocol('https', function (string $uri): string {
            $localUri = str_replace('https://example.com', $this->server()->getBaseUrl(), $uri);

            return (string) file_get_contents($localUri);
        });

        $blogPostSchema = (string) file_get_contents(__DIR__ . '/fixtures/blog-post.json');
        $blogPostSchemaUri = $this->server()->getBaseUrl() . '/blog-post.schema.json';

        $userProfileSchema = (string) file_get_contents(__DIR__ . '/fixtures/user-profile.json');

        $testValue = [
            'title' => 'New Blog Post',
            'content' => 'This is the content of the blog post...',
            'publishedDate' => '2023-08-25T15:00:00Z',
            'author' => [
                'username' => 'author_user',
                'email' => 'author@example.com',
            ],
            'tags' => ['Technology', 'Programming'],
        ];

        $this->server()->addRoute(method: 'GET', uri: '/blog-post.schema.json', body: $blogPostSchema);
        $this->server()->addRoute(method: 'GET', uri: '/user-profile.schema.json', body: $userProfileSchema);

        assertThat($testValue, matchesJsonSchemaFromUri($blogPostSchemaUri));

        $this->server()->assertRoutes();
    }
}
