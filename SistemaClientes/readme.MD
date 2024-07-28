#0 Criar o projeto

#1 Modelagem de dados

 ID_Cliente INT AUTO_INCREMENT PRIMARY KEY,
 Nome VARCHAR(255) NOT NULL,
 Endereco VARCHAR(255),
 Telefone VARCHAR(20),
 RG VARCHAR(20),
 CPF VARCHAR(20),
 Email VARCHAR(255)

#2 Criar um banco de dados

CREATE DATABASE ClienteDb;
USE clientedb;
CREATE TABLE Cliente (
    ID_Cliente INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(255) NOT NULL,
    Endereco VARCHAR(255),
    Telefone VARCHAR(20),
    RG VARCHAR(20),
    CPF VARCHAR(20),
    Email VARCHAR(255)
);

#3 Configuração do Banco de Dados

Adicionar pacotes do Entity Framework Core:

dotnet add package Pomelo.EntityFrameworkCore.MySql --version 5.0.0
dotnet add package Microsoft.EntityFrameworkCore.Tools --version 5.0.0


#4 Configurar a string de conexão

Edite o arquivo appsettings.json para incluir a string de conexão ao seu banco de dados MySQL.

{
  "ConnectionStrings": {
    "DefaultConnection": "Server=localhost;Database=clientedb;User=root;Password=;"
  },
  "Logging": {
    "LogLevel": {
      "Default": "Information",
      "Microsoft.AspNetCore": "Warning"
    }
  },
  "AllowedHosts": "*"
}

#5 Crie a classe de contexto do banco de dados

Data/ApplicationDbContext.cs


#6 Criar a classe modeladora
Models/Cliente.cs

#7 Criar o controlador
Controllers/ClientesController.cs

#8 Criar as visualizações
Views/Home/Index.cshtml
Views/Home/Create.cshtml
Views/Home/Details.cshtml
Views/Home/Edit.cshtml
Views/Home/Delete.cshtml