<?php

namespace Tests\Parser;

use Core\DI;
use Mailer\Parser\AvitoAdapter;
use Tests\Helpers\TestSuite;

class AvitoAdapterTest extends TestSuite
{
	public function testParse()
	{
		$content = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'AvitoTemplate.html');

		$parser = new AvitoAdapter();
		$data = $parser->process($content, DI::get()->getConfig());

		$this->assertArrayHasKey('email', $data);
		$this->assertArrayHasKey('name', $data);

		$this->assertEquals('Анадырге Пертврочи C4"№;', $data['name']);
		$this->assertEquals('timbrk@yandex.ru', $data['email']);

		$this->assertArrayHasKey('city', $data);
		$this->assertArrayHasKey('subjectId', $data);

		$this->assertEquals('Тюмень', $data['city']);
		$this->assertEquals('', $data['subjectId']);

		$this->assertArrayHasKey('comment', $data);
		$this->assertEquals('$@$@$@$@$@$@$@#$@#$@#$@#', $data['comment']);
	}

	public function testParse2()
	{
		$content = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'AvitoTemplate2.html');

		$parser = new AvitoAdapter();
		$data = $parser->process($content, DI::get()->getConfig());

		$this->assertArrayHasKey('email', $data);
		$this->assertArrayHasKey('name', $data);

		$this->assertEquals('Тумба', $data['name']);
		$this->assertEquals('fafafafafak@yandex.ru', $data['email']);

		$this->assertArrayHasKey('city', $data);
		$this->assertArrayHasKey('subjectId', $data);

		$this->assertEquals('Тюмень', $data['city']);
		$this->assertEquals('', $data['subjectId']);

		$this->assertArrayHasKey('comment', $data);
		$this->assertEquals("тестирование \n\n\n\n3!", $data['comment']);
	}

	public function testParse3()
	{
		$content = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'AvitoTemplate3.html');

		$parser = new AvitoAdapter();
		$data = $parser->process($content, DI::get()->getConfig());

		$this->assertArrayHasKey('email', $data);
		$this->assertArrayHasKey('name', $data);

		$this->assertEquals('Мила', $data['name']);
		$this->assertEquals('uunnamed@mail.ru', $data['email']);

		$this->assertArrayHasKey('city', $data);
		$this->assertArrayHasKey('subjectId', $data);

		$this->assertEquals('Москва', $data['city']);
		$this->assertEquals('27', $data['subjectId']);

		$this->assertArrayHasKey('comment', $data);
		$this->assertEquals("тест", $data['comment']);
	}
}
 