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


namespace MovieTicketManagementSystem
{
    public partial class Form1 : Form   // form1 inharit form
    {
        string conn = @"Data Source=DESKTOP-S9J7TU2\SQLEXPRESS;Initial Catalog=movie;Integrated Security=True;";  //DB Connecting String
        
        public Form1()
        {
            InitializeComponent();
        }
        
        private void checkBox1_CheckedChanged(object sender, EventArgs e)
        {
            login_password.PasswordChar = login_showPass.Checked ? '\0' : '*';  //show password
            
        }

        private void login_signupBtn_Click(object sender, EventArgs e)
        {
            RegForm regForm = new RegForm();
            regForm.Show();

            this.Hide();

        }
        
        private void login_btn_Click(object sender, EventArgs e)
        {
            
            if (login_username.Text=="" || login_password.Text == "")
            {
                MessageBox.Show("please fill all blank fields", "Error Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            else
            {
              
                using(SqlConnection connect=new SqlConnection(conn))    //// connect to sqlServer database 
                {
                    connect.Open();                 // open database                          // using keyword automatically closes & dispose database connection & files

                    string selectData = "SELECT * FROM users WHERE username = @usern AND password = @pass AND status = 'active'";  //query 
                    using (SqlCommand cmd=new SqlCommand(selectData, connect))     //runs sql commands(SqlCommands)
                    {
                        cmd.Parameters.AddWithValue("@usern",login_username.Text.Trim());        // parameters is parsing the input values
                        cmd.Parameters.AddWithValue("@pass", login_password.Text.Trim());

                        SqlDataAdapter adapter = new SqlDataAdapter(cmd);      //execute the query 
                        DataTable table = new DataTable();         //tempory table in memory for query result/ save result in datatable

                        adapter.Fill(table);   //fill is copying the results to table 

                        if (table.Rows.Count > 0)
                        {
                            string role = table.Rows[0]["role"].ToString();

                            MessageBox.Show("Login successful", "Information Message", MessageBoxButtons.OK, MessageBoxIcon.Information);

                            if (role == "Admin")
                            {
                                AdminForm aForm = new AdminForm();
                                aForm.Show();
                            }
                            else if (role == "staff")
                            {
                                staffForm sForm = new staffForm();
                                sForm.Show();
                            }

                  
                            this.Hide();
                        }
                        else
                        {
                            MessageBox.Show("Incorract username/password", "Error Message",MessageBoxButtons.OK,MessageBoxIcon.Error);
                        }


                    }
                }
            }
        }

        private void close_Click(object sender, EventArgs e)
        {
            Application.Exit();
        }
    }
}
