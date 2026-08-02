using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Data.SqlClient; 

namespace MovieTicketManagementSystem
{
    public partial class dasboardForm : UserControl
    {
        string conn = @"Data Source=DESKTOP-S9J7TU2\SQLEXPRESS;Initial Catalog=movie;Integrated Security=True";
        public dasboardForm()
        {
            InitializeComponent();

            displayAailableMovies();
            displayTotalStaff();
            displayTotalBuys();
            displayTotalIncome();
            displayAvailableMoviesTable();
        }



        public void refreshData()
        {
            if (InvokeRequired)
            {
                Invoke((MethodInvoker)refreshData);
                return;
            }

            displayAailableMovies();
            displayTotalStaff();
            displayTotalBuys();
            displayTotalIncome();
            displayAvailableMoviesTable();
        }


        public void displayAailableMovies()
        {
            using (SqlConnection connect = new SqlConnection(conn))
            {
                connect.Open();

                string selectData = "SELECT COUNT(id) as avMovies FROM movies WHERE status = 'Available'";

                using (SqlCommand cmd = new SqlCommand(selectData,connect))
                {
                    SqlDataReader reader = cmd.ExecuteReader();

                    if (reader.Read())
                    {
                        if (reader["avMovies"] != DBNull.Value)
                        {
                            decimal totalavMovie = Convert.ToDecimal(reader["avMovies"]);
                            labelDB_AvailableMovies.Text = totalavMovie.ToString();
                        }
                    }
                }
            }
        }


        public void displayTotalStaff()
        {
            using (SqlConnection connect = new SqlConnection(conn))
            {
                connect.Open();

                string selectData = "SELECT COUNT(id) as totalStaff FROM users WHERE  role = 'staff' AND  status = 'Active'";

                using (SqlCommand cmd = new SqlCommand(selectData, connect))
                {
                    SqlDataReader reader = cmd.ExecuteReader();

                    if (reader.Read())
                    {
                        if (reader["totalStaff"] != DBNull.Value)
                        {
                            decimal totalStaff = Convert.ToDecimal(reader["totalStaff"]);
                            labelDB_TotalStaff.Text = totalStaff.ToString();
                        }
                    }
                }
            }
        }




        public void displayTotalBuys()
        {
            using (SqlConnection connect = new SqlConnection(conn))
            {
                connect.Open();

                string selectData = "SELECT COUNT(id) as totalBuys FROM buy_tickets WHERE  status = 'PAID'";

                using (SqlCommand cmd = new SqlCommand(selectData, connect))
                {
                    SqlDataReader reader = cmd.ExecuteReader();

                    if (reader.Read())
                    {
                        if (reader["totalBuys"] != DBNull.Value)
                        {
                            decimal totalBuys = Convert.ToDecimal(reader["totalBuys"]);
                            labelDB_TotalBuys.Text = totalBuys.ToString();
                        }
                    }
                }
            }
        }




        public void displayTotalIncome()
        {
            using (SqlConnection connect = new SqlConnection(conn))
            {
                connect.Open();

                string selectData = "SELECT SUM(price) as totalIncome FROM buy_tickets WHERE  status = 'PAID'";

                using (SqlCommand cmd = new SqlCommand(selectData, connect))
                {
                    SqlDataReader reader = cmd.ExecuteReader();

                    if (reader.Read())
                    {
                        if (reader["totalIncome"] != DBNull.Value)
                        {
                            decimal totalIncome = Convert.ToDecimal(reader["totalIncome"]);
                            labelDB_TotalIncome.Text = "$" + totalIncome.ToString("0.00");
                        }
                    }
                }
            }
        }


        public void displayAvailableMoviesTable()
        {
            movieData mData = new movieData();
            List<movieData> listData = mData.movieAvailableListData();
            dataGridView1.DataSource = listData;
        }
    }
}
