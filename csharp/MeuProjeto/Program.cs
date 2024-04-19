using System;
using System.Collections.Generic;

namespace MeuProjeto
{
    class Program
    {
        static void Main(string[] args)
        {
            List<IAnimal> animais = new List<IAnimal>
            {
                new Elefante(),
                new Leao(),
                new Papagaio()
            };

            foreach (var animal in animais)
            {
                Console.WriteLine($"Nome: {animal.Nome}, Tipo: {animal.Tipo}");
                animal.EmitirSom();
                Console.WriteLine("------");
            }
        }
    }
}
