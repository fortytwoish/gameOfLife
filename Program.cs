using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace ConsoleApplication1
{
    class Program
    {
        static void Main(string[] args)
        {
            string pathToCells = @"D:\_personal\cells\";
            var files = Directory.GetFiles(pathToCells);

            Dictionary<string,string> listOfFileCoordinates = new Dictionary<string, string>();

            foreach (var file in files)
            {
                StreamReader reader = File.OpenText(file);
                string line;
                bool first = true;
                int midRow = 0;
                int midCol = 0;
                var tmpString = "[";
                var fileName = file;
                while ((line = reader.ReadLine()) != null)
                {
                    var tmpRow = 1;
                    var tmpCol = 1;

                    if (first)
                    {
                        var param = line.Split(' ');
                        midRow = (int.Parse(param[0]) / 2);
                        midCol = (int.Parse(param[1]) / 2);
                        first = false;
                    }
                    var lineChars = line.ToCharArray();
                    //["-3:-3",".....
                    if (lineChars[0].Equals('.') || lineChars[0].Equals('O'))
                    {

                        foreach (var value in lineChars)
                        {
                            var foundRow = 0;
                            var foundCol = 0;


                            if (value.Equals('O'))
                            {
                                foundCol = midCol - (midCol - tmpCol) - midCol;
                                foundRow = midRow - (midRow - tmpRow) - midRow;

                                tmpString += "\"" + foundRow + ":" + foundCol + "\"" + ",";

                                tmpCol++;
                            }
                            else
                                tmpCol++;
                        }

                        tmpRow++;
                    }
                }
                tmpString = tmpString.Remove(tmpString.Length-1);
                tmpString += "]";
                listOfFileCoordinates.Add(fileName,tmpString);
            }

            using (StreamWriter file = new StreamWriter("cellCoordinates.txt"))
                foreach (var entry in listOfFileCoordinates)
                    file.WriteLine("{0} :  {1} " + Environment.NewLine + Environment.NewLine, entry.Key, entry.Value); 
        }
    }
}
