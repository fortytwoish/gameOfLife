using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;

namespace ConsoleApplication1
{
    static class Program
    {
        // ReSharper disable once UnusedParameter.Local
        static void Main(string[] args)
        {
            if (args == null) throw new ArgumentNullException("args");
            string pathToCells = @"D:\_personal\cells\";
            var files = Directory.GetFiles(pathToCells);

            Dictionary<string,string> listOfFileCoordinates = new Dictionary<string, string>();

            foreach (var file in files)
            {
                StreamReader reader = File.OpenText(file);
                string line;
                bool first = true;
                var tmpString = "[";
                var fileName = file;

                int midRow = (File.ReadLines(file).Count() - 2) / 2;
                var tmpRow = 1;
                var lineCharCounter = 0;
                while ((line = reader.ReadLine()) != null)
                {
                    //get Line count
                    int midCol = 0;
                    if ((line.StartsWith(".") || line.StartsWith("O")) && first)
                    {
                        midCol = line.ToCharArray().Length /2;
                        first = false;
                    }
                    else if (line.StartsWith("!")) continue;

                    var tmpCol = 1;


                    var lineChars = line.ToCharArray();
                    //["-3:-3",".....
                    if (!lineChars[0].Equals('.') && !lineChars[0].Equals('O')) continue;

                    foreach (var value in lineChars)
                    {
                        if (value.Equals('O'))
                        {
                            var foundCol = midCol - (midCol - tmpCol) - midCol;
                            var foundRow = midRow - (midRow - tmpRow) - midRow;

                            tmpString += "\"" + foundRow + ":" + foundCol + "\"" + ",";
                            lineCharCounter++;
                            tmpCol++;
                        }
                        else
                            tmpCol++;

                        if (lineCharCounter > 6)
                        {
                            tmpString += Environment.NewLine;
                            lineCharCounter = 0;
                        }
                    }
                    // ReSharper disable once RedundantAssignment
                    tmpRow++;
                }
                tmpString = tmpString.Remove(tmpString.Length-1);
                tmpString += "]";
                listOfFileCoordinates.Add(fileName,tmpString);
            }



            using (StreamWriter file = new StreamWriter("cellCoordinates.txt"))
                foreach (var entry in listOfFileCoordinates)
                {

                    var shapeName = entry.Key.Substring(entry.Key.LastIndexOf(@"\", StringComparison.Ordinal)+1).Replace(".cells", "");

                    file.WriteLine("{0} :  {1} " + Environment.NewLine + Environment.NewLine,"'"+ shapeName +"'", entry.Value); 

                    }
        }
    }
}
