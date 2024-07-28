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

// Estrutura da Pilha
typedef struct{
    Book book[MAX];
    int top;
} Stack;

// Função para inicializar a pilha
void initializeStack(Stack*stack) {
    stack->top = -1;
}

// Função para verificar se a pilha está cheia
int isFull(Stack* stack) {
    return stack->top == MAX - 1;
}

// Função para verificar se a pilha está vazia
int isEmpty(Stack* stack) {
    return stack->top == -1;
}

// Função para adicionar um livro na pilha (push)
void push(Stack* stack, Book Book) {
    if(isFull(stack)) { 
        printf("A pilha está cheia");
    } else {
        stack->top++;
        stack->books[stack->top] = book;
        printf("Livro adicionado na pilha: %s\n", book.title);
    }
}
// Função para remover um livro da pilha (pop)
Book pop(Stack* stack) {
    if (isEmpty(stack)) {
        printf("A pilha está vazia. Não há livros para remover.\n");
        Book emptyBook = {"", "", 0};
        return emptyBook;
    } else {
        Book book = stack->books[stack->top];
        stack->top--;
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
    // Declaração da pilha
    Stack stack;

    // Inicialização da pilha
    initializeStack(&stack);

    // Loop para cadastrar e adicionar livros na pilha
    for(int i = 0; i < MAX; i++) {
        printf("\nCadastro do Livro %d\n", i + 1);

        // Criação de um livro através da função createBook
        Book book = createBook();

        // Adiciona o livro na pilha
        push(&stack, book);

    }

    printf("\nTodos os livros foram cadastrados e adicionados na pilha.\n");
   
    // Loop para remover e mostrar todos os livros da pilha
    while (!isEmpty(&stack)) {
        
        // Remove o livro do topo da pilha
        Book book = pop(&stack);

        
        // Exibe as informações do livro removido
        printf("Livro removido da pilha: %s por %s (%d)\n", book.title, book.author, book.year);
        
    }

    return 0;
}