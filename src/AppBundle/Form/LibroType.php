<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class LibroType extends AbstractType{
	public function buildForm(FormBuilderInterface $builder, array $options){
		$builder
			->add('isbn',TextType::class)
			->add('titulo',TextType::class)
			->add('autor',TextType::class)
			->add('editorial',TextType::class)
			->add('anio',TextType::class)
			->add('paginas',TextType::class)
			->add('ubicacion',TextType::class);
	}

	public function configureOptions(OptionsResolver $resolver){
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Entity\Libro'
			));
	}
}