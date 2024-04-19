public class Papagaio : IAnimal
{
    public string Nome => "Loro";
    public string Tipo => "Ave";

    public void EmitirSom()
    {
        Console.WriteLine("O papagaio fazendo barulho: Hello, World!");
    }
}
