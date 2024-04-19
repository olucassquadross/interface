public class Leao : IAnimal
{
    public string Nome => "Simba";
    public string Tipo => "Mamífero";

    public void EmitirSom()
    {
        Console.WriteLine("O leão fazendo barulho: Rugido!");
    }
}
