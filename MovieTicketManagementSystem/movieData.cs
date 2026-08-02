using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Data;
using System.Data.SqlClient;

namespace MovieTicketManagementSystem
{
    class movieData
    {
        string conn = @"Data Source=DESKTOP-S9J7TU2\SQLEXPRESS;Initial Catalog=movie;Integrated Security=True;";

        public int ID { get; set; } // 0
        public string MovieID { get; set; } //1
        public string MovieName { get; set; }  //2
        public string Genre { get; set; }  // 3
        public string Price { get; set; }  // 4
        public string Capacity { get; set; }  //5
        public string Status { get; set; }  //6
        public string image { get; set; }  //7
        public string Date { get; set; }  //8

        public List<movieData> movieListData()
        {
            List<movieData> listData = new List<movieData>();

            using (SqlConnection connect = new SqlConnection(conn))
            {
                connect.Open();

                string selectData = "SELECT * FROM movies WHERE delete_date IS NULL";
                
                using(SqlCommand cmd=new SqlCommand(selectData, connect))
                {
                    SqlDataReader reader = cmd.ExecuteReader();
                    while (reader.Read())
                    {
                        movieData mData = new movieData();

                        mData.ID=Convert.ToInt32(reader["ID"]);
                        mData.MovieID = reader["movies_id"].ToString();
                        mData.MovieName = reader["movies_name"].ToString();
                        mData.Genre = reader["genre"].ToString();
                        mData.Price = reader["price"].ToString();
                        mData.Capacity = reader["capacity"].ToString();
                        mData.Status = reader["status"].ToString();
                        mData.image = reader["movies_image"].ToString();
                        mData.Date = reader["created_at"].ToString();

                        listData.Add(mData);
                    }
                }
            }
            return listData;    
        }


        public List<movieData> movieAvailableListData()
        {
            List<movieData> listData = new List<movieData>();

            using (SqlConnection connect = new SqlConnection(conn))
            {
                connect.Open();

                string selectData = "SELECT * FROM movies WHERE status = 'Available' AND delete_date IS NULL";

                using (SqlCommand cmd = new SqlCommand(selectData, connect))
                {
                    SqlDataReader reader = cmd.ExecuteReader(); 
                    while (reader.Read())
                    {
                        movieData mData = new movieData();

                        mData.ID = Convert.ToInt32(reader["ID"]);
                        mData.MovieID = reader["movies_id"].ToString();
                        mData.MovieName = reader["movies_name"].ToString();
                        mData.Genre = reader["genre"].ToString();
                        mData.Price = reader["price"].ToString();
                        mData.Capacity = reader["capacity"].ToString();
                        mData.Status = reader["status"].ToString();
                        mData.image = reader["movies_image"].ToString();
                        mData.Date = reader["created_at"].ToString();


                        listData.Add(mData);
                    }
                }
            }
            return listData;
        }
    }

  
}
