import os
from langchain_community.document_loaders import PyPDFLoader, UnstructuredImageLoader 
from langchain_community.vectorstores import FAISS
from langchain_openai import OpenAIEmbeddings
from langchain_openai import ChatOpenAI
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain.chains import ConversationalRetrievalChain
from langchain.memory import ConversationBufferMemory
from datetime import datetime
from dotenv import load_dotenv 
from flask import Flask, request, jsonify
from flask_cors import CORS

load_dotenv()

app = Flask(__name__)
CORS(app)

# Khởi tạo mô hình ngôn ngữ
llm = ChatOpenAI(
    model="gpt-3.5-turbo-16k",
    temperature=0.7,
    max_tokens=500,
    timeout=None,
    max_retries=2,
)

# Hàm để tải và xử lý tất cả các tệp PDF và hình ảnh trong một thư mục
def load_documents_from_directory(directory):
    documents = []
    for filename in os.listdir(directory):
        file_path = os.path.join(directory, filename)
        if filename.endswith('.pdf'):
            loader = PyPDFLoader(file_path)
            docs = loader.load_and_split()
            for doc in docs:
                doc.metadata['source'] = filename
            documents.extend(docs)
        elif filename.lower().endswith(('.png', '.jpg', '.jpeg', '.gif', '.bmp')):
            loader = UnstructuredImageLoader(file_path)
            docs = loader.load()
            for doc in docs:
                doc.metadata['source'] = filename
            documents.extend(docs)
    return documents

# Thư mục chứa các tệp PDF và hình ảnh
data_directory = "data"

# Tải tất cả tài liệu
all_documents = load_documents_from_directory(data_directory)

# Tạo text splitter
text_splitter = RecursiveCharacterTextSplitter(
    chunk_size=1000,
    chunk_overlap=200,
    length_function=len,
)

# Chia nhỏ tài liệu
split_docs = text_splitter.split_documents(all_documents)

# Tạo chỉ mục FAISS
embeddings = OpenAIEmbeddings()
vectorstore = FAISS.from_documents(split_docs, embeddings)

# Tạo memory để lưu trữ lịch sử hội thoại
memory = ConversationBufferMemory(
    memory_key="chat_history",
    return_messages=True,
    output_key="answer"
)

# Tạo chuỗi QA với ConversationalRetrievalChain
qa_chain = ConversationalRetrievalChain.from_llm(
    llm=llm,
    retriever=vectorstore.as_retriever(search_kwargs={"k": 3}),
    memory=memory,
    get_chat_history=lambda h: h,
    return_source_documents=True,
    verbose=True,
)

@app.route('/answer', methods=['POST'])
def answer():
    data = request.json
    question = data['question']
    
    prompt = f"""Dựa trên thông tin trong các tài liệu PDF và hình ảnh, hãy trả lời câu hỏi sau một cách chính xác và linh hoạt bằng tiếng Việt. Nếu câu trả lời không có trong tài liệu, hãy nói rằng bạn không có thông tin về điều đó. Câu hỏi: {question}

    Hãy đảm bảo:
    1. Trả lời chính xác dựa trên nội dung trong tài liệu.
    2. Nếu câu hỏi không liên quan đến nội dung trong tài liệu, hãy nói rằng bạn không có thông tin về điều đó.
    3. Trả lời một cách tự nhiên và linh hoạt, không chỉ lặp lại nguyên văn từ tài liệu.
    4. Nếu cần thiết, hãy kết hợp thông tin từ nhiều phần của tài liệu để đưa ra câu trả lời đầy đủ.
    5. Sử dụng ngôn ngữ phù hợp và dễ hiểu với người đọc.
    6. Giới hạn câu trả lời trong khoảng 2-3 câu.
    Câu trả lời:"""

    result = qa_chain({"question": prompt})
    answer = result["answer"]
    source_documents = result["source_documents"]
    
    sources = [{"source": doc.metadata.get('source', 'Không có thông tin'), 
                "page": doc.metadata.get('page', 'N/A')} 
               for doc in source_documents]
    
    return jsonify({"answer": answer, "sources": sources})

if __name__ == '__main__':
    app.run(debug=True, port=5000)