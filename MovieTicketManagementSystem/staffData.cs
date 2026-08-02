using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Data;
using System.Data.SqlClient;      // SQL Server Support

namespace MovieTicketManagementSystem
{
    class staffData
    {
        // Global Variable
        string conn = @"Data Source=DESKTOP-S9J7TU2\SQLEXPRESS;Initial Catalog=movie;Integrated Security=True;";    // Connecting String for SQL Server


        public int Id { set; get; }  //0
        public string Username { set; get; }   //1
        public string password { set; get; }  //2
        public string role { set; get; }  //3
        public string status { set; get; }  //4


        public List<staffData> staffdataListData()
        {
            List<staffData> listData = new List<staffData>();

            using(SqlConnection connect=new SqlConnection(conn))
            {
                connect.Open();

                string selectData = "SELECT*FROM users WHERE role='staff' AND status != 'Deleted'";

                using(SqlCommand cmd=new SqlCommand(selectData, connect))
                {
                    SqlDataReader reader = cmd.ExecuteReader();

                    while (reader.Read())
                    {
                        staffData sData = new staffData();

                        sData.Id = (int)reader[0];
                        sData.Username = reader[1].ToString();
                        sData.password = reader[2].ToString();
                        sData.role = reader[3].ToString();
                        sData.status = reader[4].ToString();

                        listData.Add(sData);
                    }

                }

            }
            return listData;
        }
    }
}
