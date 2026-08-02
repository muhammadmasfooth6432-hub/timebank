using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Data;
using System.Data.SqlClient;
using System.IO;

namespace MovieTicketManagementSystem
{
    public partial class AddMovieForm : UserControl
    {
        string conn = @"Data Source=DESKTOP-S9J7TU2\SQLEXPRESS;Initial Catalog=movie;Integrated Security=True;";
        public AddMovieForm()
        {
            InitializeComponent();

            displayData();
        }

        public void refreshData()
        {
            if (InvokeRequired)
            {
                Invoke((MethodInvoker)refreshData);
                return;
            }
            displayData();
        }



        public void displayData()
        {
            movieData mData = new movieData();

            List<movieData> lisData = mData.movieListData();
            dataGridView1.DataSource = lisData;
        }

        private void buttonMovieImport_Click(object sender, EventArgs e)
        {
            try
            {
                OpenFileDialog file = new OpenFileDialog();
                string imagePath = "";
                file.Filter = "Image Files (*.jpg;*.png)|*.jpg;*.png";

                if (file.ShowDialog() == DialogResult.OK)
                {
                    imagePath = file.FileName;
                    pictureBox1.ImageLocation = imagePath;
                }
            }
            catch ( Exception ex)
            {

                MessageBox.Show($"Error:{ex}","ERROR Message",MessageBoxButtons.OK,MessageBoxIcon.Error);
            }
        }

        private void buttonAddMovie_Click(object sender, EventArgs e)
        {
            try
            {
                using(SqlConnection connect=new SqlConnection(conn))
                {
                    connect.Open();
                    string checkID = "SELECT movies_id FROM movies WHERE movies_id=@movieID";

                    using(SqlCommand cID=new SqlCommand(checkID, connect))
                    {
                        cID.Parameters.AddWithValue("@movieID", textBoxMovieID.Text.Trim());

                        SqlDataAdapter adapter = new SqlDataAdapter(cID);
                        DataTable table = new DataTable();

                        adapter.Fill(table);

                        if(table.Rows.Count > 0)
                        {
                            MessageBox.Show($"Movie ID: "+textBoxMovieID.Text.Trim()+ "is taken already.","ERROR Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
                        }
                        else
                        {
                            string insertData = "INSERT INTO movies (movies_id,movies_name,genre,price,capacity,movies_image,status,created_at)" + 
                                "VALUES(@movieID,@movieName,@genre,@price,@capacity,@movieImage,@status,@date)";

                            string path = Path.Combine(@"C:\Users\FMT\Desktop\movie\MovieTicketManagementSystem\MovieTicketManagementSystem\Movie_Directory\"
                                  + textBoxMovieID.Text.Trim()+ ".jpg");

                            string directoryPath = Path.GetDirectoryName(path);

                            if (!Directory.Exists(directoryPath))
                            {
                                Directory.CreateDirectory(directoryPath);
                            }

                            File.Copy(pictureBox1.ImageLocation, path, true);

                            using (SqlCommand cmd = new SqlCommand(insertData, connect))
                            {
                                cmd.Parameters.AddWithValue("@movieID", textBoxMovieID.Text.Trim());
                                cmd.Parameters.AddWithValue("@movieName", textBoxMovieName.Text.Trim());
                                cmd.Parameters.AddWithValue("@genre", comboBoxMovieGenre.SelectedItem.ToString());
                                cmd.Parameters.AddWithValue("@price", textBoxMoviePrice.Text.Trim());
                                cmd.Parameters.AddWithValue("@capacity", textBoxMovieCapacity.Text.Trim());
                                cmd.Parameters.AddWithValue("@movieImage", path);
                                cmd.Parameters.AddWithValue("@status", comboBoxStatus.SelectedItem.ToString());

                                DateTime today = DateTime.Now;
                                cmd.Parameters.AddWithValue("@date", today);

                                cmd.ExecuteNonQuery();

                                displayData();
                                clearFields();

                                MessageBox.Show("Added Successful", "Information Message", MessageBoxButtons.OK, MessageBoxIcon.Information);
                            }

                        }
                    }
                }
            }                                                                      
            catch (Exception ex)
            {

                MessageBox.Show($"Error:{ex}", "ERROR Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        public void clearFields()
        {
            textBoxMovieID.Text = "";
            textBoxMovieName.Text = "";
            comboBoxMovieGenre.SelectedIndex = -1;
            textBoxMoviePrice.Text = "";
            textBoxMovieCapacity.Text = "";
            pictureBox1.Image = null;
            comboBoxStatus.SelectedIndex = -1;
        }

        private void buttonClearMovie_Click(object sender, EventArgs e)
        {
            clearFields();
        }


        private int id = 0;
        private void dataGridView1_CellClick(object sender, DataGridViewCellEventArgs e)
        {
            if (e.RowIndex != -1)
            {
                DataGridViewRow row = dataGridView1.Rows[e.RowIndex];

                id = (int)row.Cells[0].Value;
                textBoxMovieID.Text = row.Cells[1].Value.ToString();
                textBoxMovieName.Text = row.Cells[2].Value.ToString();
                comboBoxMovieGenre.Text = row.Cells[3].Value.ToString();
                textBoxMoviePrice.Text = row.Cells[4].Value.ToString();
                textBoxMovieCapacity.Text = row.Cells[5].Value.ToString();
                comboBoxStatus.Text = row.Cells[6].Value.ToString();

                pictureBox1.ImageLocation = row.Cells[7].Value.ToString();
            }
        }

        private void buttonUpdateMovie_Click(object sender, EventArgs e)
        {
            try
            {
                if (MessageBox.Show("Are you sure you want to Update ID:"+ textBoxMovieID.Text + "?","Confirmation Message",MessageBoxButtons.YesNo,MessageBoxIcon.Question)==DialogResult.Yes)
                {
                    using (SqlConnection connect = new SqlConnection(conn))
                    {
                        connect.Open();
                        string checkID = "SELECT COUNT(id) FROM movies WHERE movies_id=@movieID";

                        using (SqlCommand cID = new SqlCommand(checkID, connect))
                        {
                            cID.Parameters.AddWithValue("@movieID", textBoxMovieID.Text.Trim());

                            SqlDataAdapter adapter = new SqlDataAdapter(cID);
                            DataTable table = new DataTable();

                            adapter.Fill(table);

                            if (table.Rows.Count >= 2)
                            {
                                MessageBox.Show($"Movie ID: " + textBoxMovieID.Text.Trim() + "is taken already.", "ERROR Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
                            }
                            else
                            {
                                string updateData = "UPDATE movies SET movies_id=@movieID,movies_name=@movieName,genre=@genre,price=@price" +
                                    ",capacity=@capacity,status=@status,update_date=@updateDate WHERE id=@id";


                                using (SqlCommand cmd = new SqlCommand(updateData, connect))
                                {
                                    cmd.Parameters.AddWithValue("@movieID", textBoxMovieID.Text.Trim());
                                    cmd.Parameters.AddWithValue("@movieName", textBoxMovieName.Text.Trim());
                                    cmd.Parameters.AddWithValue("@genre", comboBoxMovieGenre.SelectedItem.ToString());
                                    cmd.Parameters.AddWithValue("@price", textBoxMoviePrice.Text.Trim());
                                    cmd.Parameters.AddWithValue("@capacity", textBoxMovieCapacity.Text.Trim());
                                    cmd.Parameters.AddWithValue("@status", comboBoxStatus.SelectedItem.ToString());

                                    DateTime today = DateTime.Today;
                                    cmd.Parameters.AddWithValue("@updateDate", today);
                                    cmd.Parameters.AddWithValue("@id", id);

                                    cmd.ExecuteNonQuery();

                                    displayData();
                                    clearFields();

                                    MessageBox.Show("Update Successful", "Information Message", MessageBoxButtons.OK, MessageBoxIcon.Information);
                                }

                            }
                        }
                    }
                } 
            }
            catch (Exception ex)
            {

                MessageBox.Show($"Error:{ex}", "ERROR Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private void buttonDeleteMovie_Click(object sender, EventArgs e)
        {
            try
            {
                if (MessageBox.Show("Are you sure you want to Delete ID:" + textBoxMovieID.Text + "?", "Confirmation Message", MessageBoxButtons.YesNo, MessageBoxIcon.Question) == DialogResult.Yes)
                {
                    using (SqlConnection connect = new SqlConnection(conn))
                    {
                        connect.Open();
            
                        string updateData = "UPDATE movies SET delete_date=@deleteDate,status=@status WHERE id=@id";

                        using (SqlCommand cmd = new SqlCommand(updateData, connect))
                        {
                         
                            DateTime today = DateTime.Today;
                            cmd.Parameters.AddWithValue("@deleteDate", today);
                            cmd.Parameters.AddWithValue("@status","Deleted");
                            cmd.Parameters.AddWithValue("@id", id);

                            cmd.ExecuteNonQuery();

                            displayData();
                            clearFields();

                            MessageBox.Show("Delete Successful", "Information Message", MessageBoxButtons.OK, MessageBoxIcon.Information);
                        }

                    }
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show($"Error:{ex}", "ERROR Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }
    }
}
