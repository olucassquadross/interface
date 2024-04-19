public  class Elefante : IAnimal
{
    public string Nome => "Dumbo";
    public string Tipo => "Mamífero";
    public void EmitirSom() {
        Console.WriteLine("O elefante fazendo barulho");
    }
}