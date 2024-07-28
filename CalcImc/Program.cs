using System;

namespace CalculadoraIMC
{
 public class CalculadoraIMC
 {
  static void Main(string[] args)
  {
   Console.WriteLine(" Calculadora de IMC");

   Console.WriteLine("Digite seu peso em kg: ");
   double peso = Convert.ToDouble(Console.ReadLine());

   Console.WriteLine("Digite sua altra em metros: ");
   double altura = Convert.ToDouble(Console.ReadLine());

   double imc = peso / (altura * altura);
   Console.WriteLine($"O seu IMC é: {imc:F2}");
  }
 }
}