//declaração de bibliotecas
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

// Definir as constantes
#define MAX 5
#define TITLE_LENGTH 50
#define AUTHOR_LENGTH 50

// Estrutura do Livro
typedef struct {
    char title[TITLE_LENGTH];
    char author[AUTHOR_LENGTH];
    int year;
} Book;

// Estrutura da Fila
typedef struct{
    Book book[MAX];
    int front
    int rear;
    int size;
} Queue;

// Função para inicializar a fila
void initializeQueue(Queue* queue) {
    queue->front = 0;
    queue->rear = -1;
    queue->size = 0;
}

// Função para verificar se a fila está cheia
int isFull(Queue* queue) {
    return queue->size == MAX;
}


// Função para verificar se a fila está vazia
int isEmpty(Queue* queue) {
    return queue->size == 0;
}

// Função para adicionar um livro na fila (enqueue)
void enqueue(Queue* queue, Book book) {
    if (isFull(queue)) {
        printf("A fila está cheia\n");
    } else {
        queue->rear = (queue->rear + 1) % MAX;
        queue->books[queue->rear] = book;
        queue->size++;
        printf("Livro adicionado na fila: %s\n", book.title);
    }
}
// Função para remover um livro da fila (dequeue)
Book dequeue(Queue* queue) {
    if (isEmpty(queue)) {
        printf("A fila está vazia. Não há livros para remover.\n");
        Book emptyBook = {"", "", 0};
        return emptyBook;
    } else {
        Book book = queue->books[queue->front];
        queue->front = (queue->front + 1) % MAX;
        queue->size--;
        return book;
    }
}

// Função para cadastrar um livro
Book createBook() {
    Book book;
    printf("Digite o título do livro: ");
    fgets(book.title, TITLE_LENGTH, stdin);
    book.title[strcspn(book.title, "\n")] = '\0';

    printf("Digite o autor do livro: ");
    fgets(book.author, AUTHOR_LENGTH, stdin);
    book.author[strcspn(book.author, "\n")] = '\0';  // Remover o '\n' do final

    printf("Digite o ano de publicação: ");
    scanf("%d", &book.year);
    getchar();

    return book;
}


int main(){
    // Declaração da fila
    Queue queue;

    // Inicialização da fila
    initializeQueue(&queue);

    // Loop para cadastrar e adicionar livros na fila
    for (int i = 0; i < MAX; i++) {
        printf("\nCadastro do Livro %d\n", i + 1);

        // Criação de um livro através da função createBook
        Book book = createBook();

        // Adiciona o livro na fila
        enqueue(&queue, book);
    }

    printf("\nTodos os livros foram cadastrados e adicionados na fila.\n");
   
    // Loop para remover e mostrar todos os livros da fila
    while (!isEmpty(&queue)) {
        // Remove o livro do início da fila
        Book book = dequeue(&queue);

        // Exibe as informações do livro removido
        printf("Livro removido da fila: %s por %s (%d)\n", book.title, book.author, book.year);
    }

    return 0;
}