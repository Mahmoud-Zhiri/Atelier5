<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/book', name: 'add')]
    public function addBook(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $book->setPublished(true); // initialisation de "published" à True
            $author = $book->getAuthor();
            $author->setNbBooks($author->getNbBooks() + 1); // incrémentation de "nb_books" de l'auteur
            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($book);
            $entityManager->flush();
    
            return $this->redirectToRoute('list_books');
        }
    
        return $this->render('book/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/book1', name: 'list')]
    public function listBooks(): Response
{
    $books = $this->getRepository(Book::class)->findBy(['published' => true]);

    return $this->render('book/list.html.twig', [
        'books' => $books,
    ]);
}
#[Route('/book2', name: 'edit')]
public function editBook(Request $request, Book $book, ManagerRegistry $entityManager): Response
{
    $form = $this->createForm(BookType::class, $book);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Traitement de la modification du livre
        $entityManager->getManager()->flush();

        return $this->redirectToRoute('list_books');
    }

    return $this->render('book/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}
#[Route('/book3', name: 'delete')]
public function deleteBook(Book $book, ManagerRegistry $entityManager): Response
{
    $entityManager = $entityManager->getManager();
    $entityManager->remove($book);
    $entityManager->flush();

    // Vous pouvez également vérifier si l'auteur n'a plus de livres et le supprimer ici
    // Supprimer l'auteur si son "nb_books" est égal à zéro
    $author = $book->getAuthor();
    if ($author && $author->getNbBooks() === 0) {
        $entityManager->remove($author);
        $entityManager->flush();
    }

    return $this->redirectToRoute('list_books');
}
#[Route('/book4', name: 'show')]
public function showBook(Book $book): Response
{
    return $this->render('book/show.html.twig', [
        'book' => $book,
    ]);
}

}
